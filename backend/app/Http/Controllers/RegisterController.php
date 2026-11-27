<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\RfidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran warga baru mandiri.
     */
    public function showRegistrationForm(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'super_admin') {
                return redirect()->route('superadmin.index');
            } elseif ($user->role === 'satpam') {
                return redirect()->route('satpam.console');
            } elseif ($user->role === 'warga') {
                return redirect()->route('warga.index');
            }
            return redirect()->route('dashboard');
        }

        $tenants = Tenant::where('is_active', true)->orderBy('name')->get();

        $selectedTenantId = $request->query('tenant_id') ?? $tenants->first()?->id;
        $selectedTenant = $selectedTenantId ? Tenant::find($selectedTenantId) : $tenants->first();

        $houses = $selectedTenant
            ? House::where('tenant_id', $selectedTenant->id)
                ->orderBy('block_name')
                ->orderBy('house_number')
                ->get()
            : collect();

        return view('pages.register', compact('tenants', 'selectedTenant', 'houses'));
    }

    /**
     * Endpoint API JSON untuk mengambil daftar blok/nomor rumah berdasarkan tenant.
     */
    public function getHousesByTenant(Tenant $tenant)
    {
        $houses = House::where('tenant_id', $tenant->id)
            ->select('id', 'block_name', 'house_number', 'full_address')
            ->orderBy('block_name')
            ->orderBy('house_number')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $houses,
        ]);
    }

    /**
     * Proses pendaftaran warga baru (Mendukung Opsi Akun Gmail & Opsi Berkas KTP).
     */
    public function register(Request $request)
    {
        $method = $request->input('registration_method', 'gmail');

        $rules = [
            'registration_method' => 'required|in:gmail,ktp',
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users,email',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:6|confirmed',
            'house_id' => 'nullable|exists:houses,id',
            'new_block_name' => 'nullable|string|max:20',
            'new_house_number' => 'nullable|string|max:20',
            'is_family_head' => 'nullable|boolean',
        ];

        if ($method === 'ktp') {
            $rules['nik'] = 'required|digits:16|unique:users,nik';
            $rules['no_kk'] = 'nullable|digits:16';
            $rules['gender'] = 'required|in:male,female';
            $rules['birth_date'] = 'nullable|date';
            $rules['birth_place'] = 'nullable|string|max:100';
            $rules['religion'] = 'nullable|string|max:50';
            $rules['occupation'] = 'nullable|string|max:100';
            $rules['address_ktp'] = 'required|string|max:300';
            $rules['ktp_photo'] = 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';
            $rules['rfid_tag'] = 'nullable|string|max:64';
        } else {
            $rules['no_kk'] = 'nullable|digits:16';
        }

        $validated = $request->validate($rules, [
            'email.unique' => 'Alamat email ini sudah terdaftar di sistem E-WARGA.',
            'phone_number.unique' => 'Nomor WhatsApp ini sudah terdaftar di sistem E-WARGA.',
            'nik.unique' => 'Nomor Induk Kependudukan (NIK) ini sudah terdaftar.',
            'nik.digits' => 'NIK KTP harus tepat berjumlah 16 digit angka.',
            'no_kk.digits' => 'Nomor Kartu Keluarga (KK) harus tepat berjumlah 16 digit angka.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password yang dimasukkan.',
            'password.min' => 'Password minimal harus berjumlah 6 karakter.',
            'ktp_photo.max' => 'Ukuran berkas KTP maksimal 5 MB.',
            'ktp_photo.mimes' => 'Format berkas KTP harus JPG, PNG, WEBP, atau PDF.',
        ]);

        // Resolusi nomor rumah / blok & No. KK
        $houseId = $validated['house_id'] ?? null;
        if (!$houseId && !empty($validated['new_block_name']) && !empty($validated['new_house_number'])) {
            $house = House::firstOrCreate(
                [
                    'tenant_id' => $validated['tenant_id'],
                    'block_name' => strtoupper(trim($validated['new_block_name'])),
                    'house_number' => trim($validated['new_house_number']),
                ],
                [
                    'no_kk' => $validated['no_kk'] ?? null,
                    'full_address' => strtoupper(trim($validated['new_block_name'])) . ' No. ' . trim($validated['new_house_number']),
                    'occupancy_status' => 'occupied',
                ]
            );
            $houseId = $house->id;
        } elseif ($houseId && !empty($validated['no_kk']) && $request->boolean('is_family_head', true)) {
            House::where('id', $houseId)->whereNull('no_kk')->update(['no_kk' => trim($validated['no_kk'])]);
        }

        $resolvedNoKk = !empty($validated['no_kk'])
            ? trim($validated['no_kk'])
            : ($houseId ? House::where('id', $houseId)->value('no_kk') : null);

        $ktpPath = null;
        if ($method === 'ktp') {
            if ($request->hasFile('ktp_photo')) {
                $file = $request->file('ktp_photo');
                $fileName = 'ktp_reg_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $ktpPath = $file->storeAs('ktp', $fileName, 'local');
            } else {
                $ktpPath = 'uploads/ktp/sample_ktp_siti.svg';
            }
            $verificationStatus = 'pending_rw';

            // Tangkap nomor chip RFID dari scan sensor IoT jika dilampirkan
            if ($request->filled('rfid_tag')) {
                $rawTag = trim($request->input('rfid_tag'));
                $cleanTag = strtoupper(str_replace([' ', ':', '-', 'RFID'], '', $rawTag));
                $rfidTag = 'RFID-' . $cleanTag;

                // Pastikan nomor kartu fisik belum terdaftar pada akun warga lain
                $existing = User::where(function ($q) use ($rawTag, $cleanTag, $rfidTag) {
                    $q->where('rfid_tag', $rawTag)
                      ->orWhere('rfid_tag', $cleanTag)
                      ->orWhere('rfid_tag', $rfidTag)
                      ->orWhere('nik', $cleanTag);
                })->first();

                if ($existing) {
                    return redirect()->back()->withInput()->withErrors([
                        'rfid_tag' => "Nomor UID e-KTP / RFID ({$cleanTag}) sudah terdaftar atas nama warga lain ({$existing->name}).",
                    ]);
                }
            } else {
                $rfidTag = 'RFID-' . $validated['nik'];
            }
        } else {
            // Mode Pendaftaran Cepat Gmail
            $verificationStatus = 'unverified';
            $rfidTag = 'RFID-TMP-' . strtoupper(Str::random(8));
        }

        $user = User::create([
            'tenant_id' => $validated['tenant_id'],
            'house_id' => $houseId,
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone_number' => trim($validated['phone_number']),
            'password' => Hash::make($validated['password']),
            'role' => 'warga',
            'is_family_head' => $request->boolean('is_family_head', true),
            'nik' => $validated['nik'] ?? null,
            'no_kk' => $resolvedNoKk,
            'ktp_photo' => $ktpPath,
            'birth_date' => $validated['birth_date'] ?? null,
            'birth_place' => $validated['birth_place'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? 'Islam',
            'occupation' => $validated['occupation'] ?? 'Warga Tetap',
            'address_ktp' => $validated['address_ktp'] ?? null,
            'rfid_tag' => $rfidTag,
            'verification_status' => $verificationStatus,
            'gate_access_enabled' => false,
            'status' => 'active',
        ]);

        ActivityLogger::log(
            'auth.registered',
            "Warga baru {$user->name} berhasil mendaftar (" . ($verificationStatus === 'pending_rw' ? 'Verifikasi Berkas KTP' : 'Akun Gmail') . ").",
            'info',
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'method' => $method,
                'verification_status' => $verificationStatus,
            ],
            $user
        );

        Auth::login($user);
        $request->session()->put('last_activity_at', now()->timestamp);

        if ($verificationStatus === 'pending_rw') {
            $msg = "Pendaftaran berhasil! Akun warga Anda aktif. Berkas identitas KTP Anda telah diteruskan ke Pengurus RT/RW untuk verifikasi resmi. Palang gerbang RFID akan aktif otomatis setelah disetujui.";
        } else {
            $msg = "Pendaftaran berhasil dengan akun Gmail! Selamat datang di E-WARGA. Silakan lengkapi nomor KTP Anda di halaman Profil untuk mengaktifkan akses palang gerbang RFID.";
        }

        return redirect()->route('warga.index')->with('success', $msg);
    }

    /**
     * Memulai sesi pairing kartu RFID dari halaman registrasi via hardware scanner ESP32.
     * Sesi berlaku selama 60 detik di Cache berdasarkan ID lingkungan/tenant.
     */
    public function startEsp32Pairing(Request $request)
    {
        $tenantId = (int) ($request->input('tenant_id') ?? 1);

        $pairingPayload = [
            'tenant_id' => $tenantId,
            'started_at' => now()->timestamp,
        ];

        Cache::put("reg_pairing_tenant_{$tenantId}", $pairingPayload, 60);
        Cache::put('reg_pairing_global', $pairingPayload, 60);
        Cache::forget("reg_pairing_result_{$tenantId}");

        return response()->json([
            'success' => true,
            'status' => 'waiting',
            'active' => true,
            'expires_in' => 60,
            'message' => 'Sensor siap. Tempelkan e-KTP fisik Anda ke alat scanner RFID ESP32 di pos satpam / gerbang sekarang…',
        ]);
    }

    /**
     * Cek status konektivitas perangkat ESP32 (Online / Offline).
     * Mendukung heartbeat /api/rfid/heartbeat maupun /api/v1/registration-device/ping.
     */
    public function checkEsp32DeviceStatus(Request $request)
    {
        $tenantId = (int) ($request->input('tenant_id') ?? 1);
        $deviceStatus = RfidService::getDeviceOnlineStatus($tenantId, 25, true);
        $isOnline = $deviceStatus['online'];
        $secondsAgo = $deviceStatus['seconds_ago'];

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'status' => $isOnline ? 'online' : 'offline',
            'status_label' => $isOnline ? 'ESP32 Online (Terhubung ke Hotspot)' : 'ESP32 Offline (Belum Terhubung)',
            'hotspot_ssid' => 'BIAN',
            'last_seen_seconds_ago' => $secondsAgo,
            'message' => $isOnline
                ? 'Alat ESP32 sudah terhubung online. Sensor siap menerima tap kartu e-KTP.'
                : 'Alat ESP32 belum terhubung ke hotspot. Pastikan hotspot HP aktif dan ESP32 menyala.',
        ]);
    }

    /**
     * Polling hasil tap kartu dari alat ESP32 untuk formulir registrasi.
     */
    public function checkEsp32PairingStatus(Request $request)
    {
        $tenantId = (int) ($request->input('tenant_id') ?? 1);
        $pairedTag = Cache::pull("reg_pairing_result_{$tenantId}")
            ?? Cache::pull("reg_latest_tap_tenant_{$tenantId}");

        $isOnline = RfidService::getDeviceOnlineStatus($tenantId, 25, true)['online'];

        if ($pairedTag) {
            return response()->json([
                'success' => true,
                'status' => 'paired',
                'paired' => true,
                'is_online' => true,
                'rfid_tag' => $pairedTag,
                'message' => "Fisik e-KTP berhasil dideteksi di alat scanner ESP32! UID: {$pairedTag}",
            ]);
        }

        $isWaiting = Cache::has("reg_pairing_tenant_{$tenantId}") || Cache::has('reg_pairing_global');

        return response()->json([
            'success' => true,
            'status' => $isWaiting ? 'waiting' : 'expired',
            'paired' => false,
            'is_online' => $isOnline,
            'waiting' => $isWaiting,
            'message' => $isOnline
                ? 'Sensor siap. Tempelkan e-KTP fisik Anda ke alat scanner RFID ESP32 sekarang…'
                : 'Peringatan: Scanner ESP32 sedang Offline (Belum terhubung ke hotspot "BIAN").',
        ]);
    }
}
