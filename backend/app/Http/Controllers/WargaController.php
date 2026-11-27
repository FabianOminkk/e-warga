<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\GateLog;
use App\Models\House;
use App\Models\LetterRequest;
use App\Models\Payment;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Services\ActivityLogger;
use App\Services\RfidService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WargaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $tenant = Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first();
        $smartGate = RfidService::resolvePrimaryGate($tenant?->id);
        $isGateOnline = RfidService::getGateOnlineStatus($tenant?->id)['online'];

        // Tagihan Warga
        $billing = null;
        $billings = collect();
        $latestPayment = null;
        if ($user && $user->house_id) {
            $billings = Billing::where('house_id', $user->house_id)
                ->with(['payments' => fn ($q) => $q->latest()])
                ->latest('billing_period')
                ->take(6)
                ->get();
            $billing = $billings->first();
            $latestPayment = $billing?->payments?->first();
        }

        // Tamu Aktif Warga Ini
        $myVisitors = $user 
            ? Visitor::where('invited_by_user_id', $user->id)->latest()->take(5)->get()
            : collect();

        // Riwayat Akses Gerbang Rumah / Warga Ini
        $myGateLogs = $user
            ? GateLog::where('user_id', $user->id)->latest('triggered_at')->take(5)->get()
            : collect();

        // Surat Pengantar Milik Warga Ini
        $myLetters = $user
            ? LetterRequest::where('user_id', $user->id)->latest()->take(4)->get()
            : collect();

        // Daftar Rumah di Komplek Ini (untuk form pengaturan profil warga)
        $houses = House::where('tenant_id', $tenant?->id)->orderBy('block_name')->orderBy('house_number')->get();

        return view('pages.warga', compact('user', 'tenant', 'smartGate', 'isGateOnline', 'billing', 'billings', 'latestPayment', 'myVisitors', 'myGateLogs', 'myLetters', 'houses'));
    }

    public function profile()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $tenant = Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first();
        $houses = House::where('tenant_id', $tenant?->id)->orderBy('block_name')->orderBy('house_number')->get();

        return view('pages.warga-profile', compact('user', 'tenant', 'houses'));
    }

    public function triggerOpenGate(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'warga')->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Silakan login terlebih dahulu.');
        }

        // ATURAN INTEGRASI: Cek apakah warga sudah di-ACC Ketua RW & Memiliki Akses Aktif
        if (!$user->canAccessGate()) {
            $errorMessage = $user->verification_status === 'pending_rw'
                ? 'AKSES GERBANG TERKUNCI SEMENTARA: Berkas identitas Anda sedang ditinjau dan menunggu persetujuan Ketua RW (Bpk. H. Rahmat Hidayat). Silakan hubungi Pak RW jika mendesak.'
                : 'AKSES GERBANG TERKUNCI TOTAL: Anda adalah warga baru dan belum melengkapi identitas KTP & tanggal lahir. Silakan lengkapi berkas di menu "Atur Akun & Berkas KTP" agar dapat diverifikasi oleh Ketua RW.';

            ActivityLogger::log(
                'gate.access_denied',
                "Percobaan buka gerbang ditolak untuk {$user->name} (status verifikasi belum memenuhi syarat).",
                'warning',
                ['verification_status' => $user->verification_status],
                $user
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'locked' => true,
                ], 403);
            }

            return redirect()->back()->with('error', $errorMessage);
        }

        $tenant = Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first();
        $gate = RfidService::resolvePrimaryGate($tenant?->id);
        $isGateOnline = RfidService::getGateOnlineStatus($tenant?->id)['online'];

        if (!$gate || !$isGateOnline) {
            if ($gate) {
                GateLog::create([
                    'tenant_id' => $tenant?->id ?? $gate->tenant_id,
                    'smart_gate_id' => $gate->id,
                    'user_id' => $user->id,
                    'access_method' => 'mobile_button',
                    'direction' => 'entry',
                    'status' => 'denied',
                    'reason' => "GAGAL BUKA PORTAL: Smart Gate ESP32 sedang OFFLINE (permintaan {$user->name})",
                    'triggered_at' => now(),
                ]);
            }

            $offlineMsg = 'Smart Gate sedang OFFLINE! Perangkat ESP32 belum terhubung ke server/hotspot.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'offline' => true,
                    'message' => $offlineMsg,
                ], 422);
            }

            return redirect()->back()->with('error', $offlineMsg);
        }

        $log = GateLog::create([
            'tenant_id' => $tenant?->id ?? $gate->tenant_id,
            'smart_gate_id' => $gate->id,
            'user_id' => $user->id,
            'access_method' => 'mobile_button',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => 'Buka Pintu via Tombol HP Warga: ' . $user->name . ' (E-KTP Terverifikasi RW)',
            'triggered_at' => now(),
        ]);

        // Kirim sinyal pemicu instan ke perangkat IoT fisik ESP32
        \Illuminate\Support\Facades\Cache::put("gate_remote_open_{$gate->id}", [
            'user_name' => $user->name,
            'reason' => 'Buka via Tombol HP: ' . $user->name,
            'time' => now()->toIso8601String(),
        ], 15);
        \Illuminate\Support\Facades\Cache::put("gate_remote_open_global", [
            'user_name' => $user->name,
            'reason' => 'Buka via Tombol HP: ' . $user->name,
            'time' => now()->toIso8601String(),
        ], 15);

        ActivityLogger::log(
            'gate.remote_open',
            "Warga {$user->name} membuka palang pintu via aplikasi.",
            'info',
            ['gate_id' => $gate->id, 'gate_name' => $gate->name],
            $user
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Palang gerbang berhasil dibuka! Sensor mengenali E-KTP & akun Anda.',
                'log_id' => $log->id,
                'duration' => 5,
            ]);
        }

        return redirect()->back()->with('success', 'Palang gerbang berhasil dibuka! Sensor mengenali E-KTP/akun terverifikasi Anda.');
    }

    public function createVisitorPass(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'warga')->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Sesi tidak valid.');
        }

        // Cek Hak Akses: Warga yang belum di-ACC RW tidak boleh menerbitkan izin tamu
        if (!$user->canAccessGate() && !$user->isPengurus()) {
            return redirect()->back()->with('error', 'Fitur Izin Tamu terkunci! Lengkapi identitas KTP dan tunggu persetujuan Ketua RW terlebih dahulu.');
        }

        $validated = $request->validate([
            'guest_name' => 'required|string|max:150',
            'guest_type' => 'required|in:guest,courier,ride_hailing,contractor',
            'vehicle_plate' => 'nullable|string|max:20',
            'duration_hours' => 'required|integer|min:1|max:48',
            'notes' => 'nullable|string|max:255',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        if (!$tenant) {
            return redirect()->back()->with('error', 'Data komplek tidak ditemukan.');
        }

        // Cari atau pastikan ada rumah yang terikat
        $hostHouseId = $user->house_id ?? $tenant->houses()->first()?->id;
        if (!$hostHouseId) {
            return redirect()->back()->with('error', 'Belum ada data unit rumah terdaftar. Hubungi pengurus RT.');
        }

        $validFrom = now();
        $validUntil = now()->addHours((int)$validated['duration_hours']);
        $qrToken = 'PASS-' . strtoupper(Str::random(10));

        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'host_house_id' => $hostHouseId,
            'invited_by_user_id' => $user->id,
            'guest_name' => $validated['guest_name'],
            'guest_type' => $validated['guest_type'],
            'approval_status' => 'approved',
            'approval_notes' => 'Izin Diterbitkan oleh Pemilik Rumah (Pre-Approved)',
            'approved_at' => now(),
            'approved_by_user_id' => $user->id,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'qr_token' => $qrToken,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'is_used' => false,
        ]);

        ActivityLogger::log(
            'visitor.pass_created',
            "Warga {$user->name} menerbitkan izin tamu untuk {$validated['guest_name']} ({$validated['guest_type']}).",
            'info',
            ['guest_name' => $validated['guest_name'], 'guest_type' => $validated['guest_type']],
            $user,
            $visitor
        );

        return redirect()->back()->with('new_visitor', $visitor);
    }

    public function panicAlert(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'warga')->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        if (!$user->canAccessGate() && !$user->isPengurus()) {
            return redirect()->back()->with('error', 'Tombol Darurat hanya dapat diaktifkan oleh warga ber-KTP terverifikasi.');
        }

        $houseNumber = $user->house ? $user->house->block_and_number : 'Lingkungan Warga';

        ActivityLogger::log(
            'security.panic_alert',
            "🚨 TOMBOL PANIK DARURAT DIAKTIFKAN oleh {$user->name} ({$houseNumber})!",
            'danger',
            ['house' => $houseNumber, 'user_name' => $user->name],
            $user
        );

        return redirect()->back()->with('panic_sent', "TOMBOL DARURAT DIAKTIFKAN! Sinyal bahaya dari {$user->name} ({$houseNumber}) telah diteruskan ke Pos Satpam dan Pengurus RT.");
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone_number' => 'required|string|max:25',
            'nik' => 'required|numeric|digits:16',
            'no_kk' => 'nullable|digits:16',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'religion' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:100',
            'address_ktp' => 'required|string|max:500',
            'house_id' => 'nullable|exists:houses,id',
            'ktp_photo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:5120',
        ], [
            'nik.digits' => 'Nomor NIK KTP wajib tepat 16 digit angka.',
            'no_kk.digits' => 'Nomor Kartu Keluarga (KK) harus tepat 16 digit angka.',
            'nik.required' => 'Nomor KTP (NIK) wajib diisi untuk verifikasi identitas resmi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_place.required' => 'Tempat lahir wajib diisi sesuai KTP.',
            'address_ktp.required' => 'Alamat domisili sesuai KTP wajib diisi.',
        ]);

        $targetHouseId = $validated['house_id'] ?? $user->house_id;
        $resolvedNoKk = !empty($validated['no_kk']) ? trim($validated['no_kk']) : ($user->no_kk ?? ($targetHouseId ? House::find($targetHouseId)?->no_kk : null));

        $dataToUpdate = [
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'nik' => $validated['nik'],
            'no_kk' => $resolvedNoKk,
            'birth_date' => $validated['birth_date'],
            'birth_place' => $validated['birth_place'],
            'gender' => $validated['gender'],
            'religion' => $validated['religion'] ?? 'Islam',
            'occupation' => $validated['occupation'] ?? 'Karyawan Swasta',
            'address_ktp' => $validated['address_ktp'],
            'house_id' => $targetHouseId,
        ];

        if ($targetHouseId && !empty($validated['no_kk']) && $user->is_family_head) {
            House::where('id', $targetHouseId)->whereNull('no_kk')->update(['no_kk' => trim($validated['no_kk'])]);
        }

        // Buat RFID Tag otomatis terintegrasi dengan NIK KTP jika belum ada
        if (!$user->rfid_tag || empty($user->rfid_tag)) {
            $dataToUpdate['rfid_tag'] = 'RFID-' . $validated['nik'];
        }

        // Upload Foto KTP jika dilampirkan (Disimpan privat untuk privasi warga & UU PDP)
        if ($request->hasFile('ktp_photo')) {
            $file = $request->file('ktp_photo');
            $fileName = 'ktp_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs('ktp', $fileName, 'local');
            $dataToUpdate['ktp_photo'] = $storedPath;
        } elseif (!$user->ktp_photo) {
            // Pasang sample svg jika baru pertama kali mengisi dan tidak upload manual
            $dataToUpdate['ktp_photo'] = 'uploads/ktp/sample_ktp_siti.svg';
        }

        // Jika warga sebelumnya unverified atau ditolak, alihkan status ke pending_rw agar ditinjau Ketua RW
        if ($user->verification_status !== 'approved') {
            $dataToUpdate['verification_status'] = 'pending_rw';
            $dataToUpdate['gate_access_enabled'] = false;
        }

        $user->update($dataToUpdate);

        ActivityLogger::log(
            'warga.profile_updated',
            "Pembaruan profil mandiri & berkas KTP oleh {$user->name}.",
            'info',
            ['nik' => substr($validated['nik'], 0, 6) . '******'],
            $user
        );

        return redirect()->route('warga.profile')->with('success', 'Data identitas KTP berhasil disimpan! Berkas Anda saat ini masuk ke antrean verifikasi Ketua RW (Bpk. H. Rahmat Hidayat). Akses palang pintu & izin tamu akan aktif otomatis setelah disetujui Pak RW.');
    }

    /**
     * Menampilkan foto KTP secara aman (hanya untuk pemilik KTP atau pengurus RW/RT/Super Admin).
     * Mematuhi UU Perlindungan Data Pribadi (UU PDP No. 27/2022).
     */
    public function showKtpPhoto($id)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        $targetUser = User::findOrFail($id);

        $isOwner = $currentUser->id === $targetUser->id;
        $sameTenant = $currentUser->isSuperAdmin() || (int) $currentUser->tenant_id === (int) $targetUser->tenant_id;
        $isOfficial = $sameTenant && in_array($currentUser->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'bendahara', 'satpam', 'sekretaris', 'pengurus_rt']);

        if (!$isOwner && !$isOfficial) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat berkas KTP warga ini.');
        }

        $photoPath = $targetUser->ktp_photo;
        if (!$photoPath) {
            abort(404, 'Berkas KTP tidak ditemukan.');
        }

        // Catat akses ke berkas KTP (Audit Trail Kepatuhan UU PDP No. 27/2022)
        ActivityLogger::log(
            'ktp.access_view',
            "Akses berkas KTP: {$targetUser->name} oleh {$currentUser->name} ({$currentUser->role}).",
            'warning',
            [
                'target_user_id' => $targetUser->id,
                'target_name' => $targetUser->name,
                'masked_nik' => substr((string) $targetUser->nik, 0, 6) . '******',
                'is_owner' => $isOwner,
            ],
            $currentUser,
            $targetUser
        );

        // 1. Cek di private storage local disk (menggunakan path disk dinamis Laravel)
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($photoPath)) {
            return response()->file(\Illuminate\Support\Facades\Storage::disk('local')->path($photoPath));
        }

        // 2. Fallback jika tersimpan langsung di storage/app/
        if (file_exists(storage_path('app/' . $photoPath))) {
            return response()->file(storage_path('app/' . $photoPath));
        }

        // 3. Fallback jika tersimpan di storage/app/private/
        if (file_exists(storage_path('app/private/' . $photoPath))) {
            return response()->file(storage_path('app/private/' . $photoPath));
        }

        // 4. Fallback jika masih berkas sample SVG di public/uploads
        if (file_exists(public_path($photoPath))) {
            return response()->file(public_path($photoPath));
        }

        abort(404, 'Berkas fisik KTP tidak ditemukan di server.');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'Password saat ini yang Anda masukkan salah.');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        ActivityLogger::log(
            'auth.password_changed',
            "Pengguna {$user->name} ({$user->role}) memperbarui kata sandi akun.",
            'warning',
            [],
            $user
        );

        return redirect()->back()->with('success', 'Password akun warga berhasil diperbarui!');
    }

    /**
     * Daftarkan UID kartu e-KTP / RFID fisik ke akun warga yang sedang login.
     * Dapat dipanggil via Web NFC di HP Android, input manual, atau hasil tap scanner.
     */
    public function pairRfid(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401)
                : redirect()->route('login');
        }

        $validated = $request->validate([
            'rfid_uid' => 'required|string|min:4|max:64',
        ], [
            'rfid_uid.required' => 'Nomor UID e-KTP / RFID fisik wajib diisi.',
            'rfid_uid.min' => 'Panjang UID e-KTP minimal 4 karakter.',
            'rfid_uid.max' => 'Panjang UID e-KTP maksimal 64 karakter.',
        ]);

        $rawUid = trim($validated['rfid_uid']);
        $cleanUid = strtoupper(str_replace([' ', ':', '-', 'RFID'], '', $rawUid));

        if (empty($cleanUid)) {
            $err = 'Format UID e-KTP tidak valid.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $err], 422)
                : redirect()->back()->with('error', $err);
        }

        $standardTag = 'RFID-' . $cleanUid;

        // Cek apakah UID sudah terdaftar pada warga lain
        $existing = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($rawUid, $cleanUid, $standardTag) {
                $q->where('rfid_tag', $rawUid)
                  ->orWhere('rfid_tag', $cleanUid)
                  ->orWhere('rfid_tag', $standardTag)
                  ->orWhere('nik', $cleanUid);
            })->first();

        if ($existing) {
            $houseInfo = $existing->house ? $existing->house->block_and_number : 'Warga Lain';
            $errMsg = "UID e-KTP ({$cleanUid}) sudah terdaftar atas nama {$existing->name} ({$houseInfo}). Satu fisik kartu tidak dapat didaftarkan ganda demi keamanan portal.";
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $errMsg], 422)
                : redirect()->back()->with('error', $errMsg);
        }

        $user->update([
            'rfid_tag' => $standardTag,
        ]);

        ActivityLogger::log(
            'rfid.paired',
            "Warga {$user->name} berhasil mendaftarkan UID fisik e-KTP: {$standardTag}.",
            'info',
            ['rfid_tag' => $standardTag, 'method' => $request->input('scan_method', 'web_nfc')],
            $user
        );

        $succMsg = "e-KTP fisik berhasil didaftarkan dengan nomor UID: {$standardTag}!";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $succMsg,
                'rfid_tag' => $standardTag,
                'clean_uid' => $cleanUid,
                'can_access_gate' => $user->canAccessGate(),
                'status_label' => $user->canAccessGate() ? 'Akses Gerbang Aktif' : 'Menunggu Konfirmasi Admin / Pengurus RT/RW',
            ]);
        }

        return redirect()->back()->with('success', $succMsg);
    }

    /**
     * Memulai sesi pairing kartu RFID via hardware scanner ESP32 gerbang / pos satpam.
     * Sesi berlaku selama 60 detik di Cache.
     */
    public function startEsp32Pairing(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $tenantId = $user->tenant_id ?? 1;

        $pairingPayload = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'tenant_id' => $tenantId,
            'started_at' => now()->timestamp,
        ];

        // Simpan sesi pairing di cache selama 60 detik (per-tenant dan global agar kompatibel dengan shared ESP32)
        Cache::put("rfid_pairing_tenant_{$tenantId}", $pairingPayload, 60);
        Cache::put('rfid_pairing_global', $pairingPayload, 60);

        // Hapus penanda hasil/error sebelumnya jika ada
        Cache::forget("rfid_pairing_success_{$user->id}");
        Cache::forget("rfid_pairing_error_{$user->id}");

        return response()->json([
            'success' => true,
            'expires_in' => 60,
            'message' => 'Mode pairing aktif! Silakan tempelkan fisik kartu e-KTP Anda ke sensor scanner ESP32 di gerbang atau pos satpam dalam waktu 60 detik.',
        ]);
    }

    /**
     * Cek status konektivitas perangkat ESP32 (Online / Offline).
     * Mendukung heartbeat /api/rfid/heartbeat maupun /api/v1/registration-device/ping.
     */
    public function checkEsp32DeviceStatus(Request $request)
    {
        $tenantId = (int) (Auth::user()?->tenant_id ?? Tenant::getActiveTenant()?->id ?? 1);
        $deviceStatus = RfidService::getDeviceOnlineStatus($tenantId, RfidService::ONLINE_TTL_SECONDS);
        $gateStatus = RfidService::getGateOnlineStatus($tenantId, RfidService::ONLINE_TTL_SECONDS);
        $isGateOnline = $gateStatus['online'];
        $isOnline = $deviceStatus['online'];
        $secondsAgo = $deviceStatus['seconds_ago'];
        $readerInfo = $deviceStatus['reader_info'] ?? $gateStatus['reader_info'];
        $deskPing = Cache::get('esp32_device_' . RfidService::DESK_DEVICE_UID . '_last_ping');
        $resolvedDeviceId = is_array($readerInfo) && !empty($readerInfo['device_id'])
            ? $readerInfo['device_id']
            : ($isOnline && is_array($deskPing) ? RfidService::DESK_DEVICE_UID : null);
        $resolvedIp = is_array($readerInfo) && !empty($readerInfo['ip'])
            ? $readerInfo['ip']
            : (is_array($deskPing) ? ($deskPing['ip'] ?? null) : null);

        $rfidVer = Cache::get("esp32_rfid_ver_tenant_{$tenantId}")
            ?? Cache::get('esp32_rfid_ver_global');
        $rfidHealthy = $rfidVer && !in_array(strtolower($rfidVer), ['0x00', '0xff', '0x0', '0xf']);

        $message = 'Alat ESP32 belum terhubung ke hotspot. Pastikan modul ESP32 menyala dan hotspot HP ("BIAN") aktif.';
        if ($isOnline) {
            if ($rfidHealthy) {
                $message = "Alat scanner ESP32 online & sensor RFID RC522 terdeteksi (Versi {$rfidVer}). Sensor siap menerima tap kartu e-KTP fisik.";
            } elseif ($rfidVer) {
                $message = "Alat ESP32 online, TETAPI modul RFID RC522 terbaca {$rfidVer} (tidak terdeteksi). Pastikan kabel RST terpasang ke GPIO 22 dan pin header disolder.";
            } else {
                $message = 'Alat ESP32 sudah terhubung online. Sensor siap menerima tap kartu e-KTP fisik.';
            }
        }

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'gate_online' => $isGateOnline,
            'status' => $isOnline ? 'online' : 'offline',
            'status_label' => $isOnline ? 'ESP32 Online (Terhubung ke Hotspot)' : 'ESP32 Offline (Belum Terhubung)',
            'hotspot_ssid' => 'BIAN',
            'last_seen_seconds_ago' => $secondsAgo,
            'device_id' => $resolvedDeviceId,
            'ip_address' => $resolvedIp,
            'rfid_ver' => $rfidVer,
            'rfid_healthy' => $rfidHealthy,
            'message' => $message,
        ]);
    }

    /**
     * Polling status pairing dari alat ESP32.
     */
    public function checkEsp32PairingStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $tenantId = $user->tenant_id ?? 1;
        $pairingSuccess = Cache::pull("rfid_pairing_success_{$user->id}");
        $pairingError = Cache::pull("rfid_pairing_error_{$user->id}");
        $deviceStatus = RfidService::getDeviceOnlineStatus($tenantId, 60);
        $isOnline = $deviceStatus['online'];

        if ($pairingSuccess) {
            $user->refresh();
            return response()->json([
                'success' => true,
                'paired' => true,
                'is_online' => true,
                'rfid_tag' => $pairingSuccess,
                'message' => "Fisik e-KTP berhasil dideteksi di alat scanner ESP32! Kartu {$pairingSuccess} kini resmi terdaftar untuk akun Anda.",
                'can_access_gate' => $user->canAccessGate(),
                'status_label' => $user->canAccessGate() ? 'Akses Gerbang Aktif' : 'Menunggu Konfirmasi Admin / Pengurus RT/RW',
            ]);
        }

        if ($pairingError) {
            return response()->json([
                'success' => false,
                'paired' => false,
                'pairing_error' => true,
                'is_online' => $isOnline,
                'message' => $pairingError,
            ]);
        }

        $isWaiting = Cache::has("rfid_pairing_tenant_{$tenantId}")
            || Cache::has('rfid_pairing_global');

        return response()->json([
            'success' => true,
            'paired' => false,
            'is_online' => $isOnline,
            'waiting' => $isWaiting,
            'message' => $isOnline
                ? 'Alat scanner ESP32 terhubung online. Tempelkan kartu e-KTP Anda ke alat sekarang…'
                : 'Perhatian: Scanner ESP32 sedang Offline (Belum terhubung ke hotspot "BIAN").',
        ]);
    }

    /**
     * Membatalkan sesi pairing ESP32.
     */
    public function cancelEsp32Pairing(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $tenantId = $user->tenant_id ?? 1;
            Cache::forget("rfid_pairing_tenant_{$tenantId}");
            Cache::forget('rfid_pairing_global');
            Cache::forget("rfid_pairing_error_{$user->id}");
        }

        return response()->json(['success' => true, 'message' => 'Sesi pairing dibatalkan.']);
    }

    /**
     * Menu & Dashboard Smart Gate Perumahan untuk Warga.
     */
    public function smartGate()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $tenant = Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first();
        $smartGate = RfidService::resolvePrimaryGate($tenant?->id);

        // 1. Cek status konektivitas ESP32 Smart Gate (hanya ONLINE jika ESP32 gerbang terhubung)
        $isGateOnline = RfidService::getGateOnlineStatus($tenant?->id)['online'];

        // 2. Daftar Permintaan Tamu & Kurir yang Menunggu Persetujuan Manual Warga Ini
        $pendingApprovals = Visitor::where('tenant_id', $tenant?->id)
            ->where(function ($q) use ($user) {
                if ($user->house_id) {
                    $q->where('host_house_id', $user->house_id);
                }
                $q->orWhere('invited_by_user_id', $user->id);
            })
            ->where('approval_status', 'pending')
            ->where('is_used', false)
            ->where('valid_until', '>=', now())
            ->with(['hostHouse', 'invitedBy'])
            ->latest()
            ->get();

        // 3. Daftar Tamu / Kurir yang Sudah Disetujui & Masih Aktif
        $activeVisitors = Visitor::where('tenant_id', $tenant?->id)
            ->where(function ($q) use ($user) {
                if ($user->house_id) {
                    $q->where('host_house_id', $user->house_id);
                }
                $q->orWhere('invited_by_user_id', $user->id);
            })
            ->where('approval_status', 'approved')
            ->where('valid_until', '>=', now())
            ->with(['hostHouse', 'invitedBy', 'satpam'])
            ->latest()
            ->take(8)
            ->get();

        // 4. Riwayat Akses Gerbang (Tap e-KTP, Remote HP, Tiket Tamu / Kurir)
        $gateLogs = GateLog::where('tenant_id', $tenant?->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->house_id) {
                    $visitorIds = Visitor::where('host_house_id', $user->house_id)->pluck('id');
                    if ($visitorIds->isNotEmpty()) {
                        $q->orWhereIn('visitor_id', $visitorIds);
                    }
                }
            })
            ->with(['user', 'visitor'])
            ->latest('triggered_at')
            ->take(15)
            ->get();

        return view('pages.warga-smartgate', compact('user', 'tenant', 'smartGate', 'isGateOnline', 'pendingApprovals', 'activeVisitors', 'gateLogs'));
    }

    /**
     * Menyetujui Izin Masuk Tamu / Kurir secara Manual dan Membuka Palang Gerbang Otomatis.
     */
    public function approveVisitor(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $visitor = Visitor::where('id', $id)->first();
        if (!$visitor) {
            return redirect()->back()->with('error', 'Data tamu / kurir tidak ditemukan.');
        }

        // Pastikan hanya warga pemilik rumah tujuan, pembuat undangan, atau pengurus yang bisa menyetujui
        $ownsHouse = $user->house_id !== null && (int) $visitor->host_house_id === (int) $user->house_id;
        $invitedByMe = (int) $visitor->invited_by_user_id === (int) $user->id;
        $isTenantManager = $user->isPengurus() && ($user->isSuperAdmin() || (int) $user->tenant_id === (int) $visitor->tenant_id);
        if (!$ownsHouse && !$invitedByMe && !$isTenantManager) {
            return redirect()->back()->with('error', 'Anda tidak berwenang menyetujui kunjungan untuk rumah lain.');
        }

        $notes = $request->input('notes', 'Disetujui oleh pemilik rumah');

        $visitor->update([
            'approval_status' => 'approved',
            'approval_notes' => $notes,
            'approved_at' => now(),
            'approved_by_user_id' => $user->id,
        ]);

        // Picu palang gerbang terbuka otomatis secara instan jika IoT ESP32 terhubung online
        $gate = RfidService::resolvePrimaryGate($visitor->tenant_id);
        $isGateOnline = RfidService::getGateOnlineStatus($visitor->tenant_id)['online'];
        $gateTriggered = false;

        if ($gate && $isGateOnline) {
            \Illuminate\Support\Facades\Cache::put("gate_remote_open_{$gate->id}", [
                'user_name' => $user->name,
                'reason' => "Izin Tamu/Kurir Disetujui Warga: {$visitor->guest_name} ({$visitor->guest_type_label})",
                'time' => now()->toIso8601String(),
            ], 15);
            \Illuminate\Support\Facades\Cache::put("gate_remote_open_global", [
                'user_name' => $user->name,
                'reason' => "Izin Tamu/Kurir Disetujui Warga: {$visitor->guest_name} ({$visitor->guest_type_label})",
                'time' => now()->toIso8601String(),
            ], 15);

            GateLog::create([
                'tenant_id' => $visitor->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id' => $user->id,
                'visitor_id' => $visitor->id,
                'access_method' => 'mobile_button',
                'direction' => 'entry',
                'status' => 'granted',
                'reason' => "Izin Masuk Disetujui Pemilik Rumah ({$user->name}) untuk {$visitor->guest_name} ({$visitor->guest_type_label})",
                'triggered_at' => now(),
            ]);
            $gateTriggered = true;
        }

        ActivityLogger::log(
            'visitor.approved',
            "Warga {$user->name} menyetujui izin masuk tamu/kurir: {$visitor->guest_name} ({$visitor->guest_type_label})" . ($gateTriggered ? ' dan membuka palang gerbang.' : ' (perangkat ESP32 offline).'),
            'success',
            ['visitor_id' => $visitor->id, 'guest_name' => $visitor->guest_name, 'guest_type' => $visitor->guest_type, 'gate_online' => $isGateOnline],
            $user,
            $visitor
        );

        $approvalMessage = $gateTriggered
            ? "Izin masuk untuk {$visitor->guest_name} telah disetujui! Palang gerbang otomatis dibuka via ESP32."
            : "Izin masuk untuk {$visitor->guest_name} telah disetujui! Catatan: Smart Gate ESP32 sedang OFFLINE sehingga akses dibuka secara manual oleh Pos Satpam.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'gate_opened' => $gateTriggered,
                'gate_online' => $isGateOnline,
                'message' => $approvalMessage,
                'visitor' => $visitor,
            ]);
        }

        return redirect()->back()->with('success', $approvalMessage);
    }

    /**
     * Menolak Izin Masuk Tamu / Kurir.
     */
    public function rejectVisitor(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $visitor = Visitor::where('id', $id)->first();
        if (!$visitor) {
            return redirect()->back()->with('error', 'Data tamu / kurir tidak ditemukan.');
        }

        $ownsHouse = $user->house_id !== null && (int) $visitor->host_house_id === (int) $user->house_id;
        $invitedByMe = (int) $visitor->invited_by_user_id === (int) $user->id;
        $isTenantManager = $user->isPengurus() && ($user->isSuperAdmin() || (int) $user->tenant_id === (int) $visitor->tenant_id);
        if (!$ownsHouse && !$invitedByMe && !$isTenantManager) {
            return redirect()->back()->with('error', 'Anda tidak berwenang menolak kunjungan ini.');
        }

        $reason = $request->input('reason', 'Ditolak oleh pemilik rumah');

        $visitor->update([
            'approval_status' => 'rejected',
            'approval_notes' => $reason,
            'approved_at' => now(),
            'approved_by_user_id' => $user->id,
        ]);

        $gate = SmartGate::where('tenant_id', $visitor->tenant_id)->first() ?? SmartGate::first();
        if ($gate) {
            GateLog::create([
                'tenant_id' => $visitor->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id' => $user->id,
                'visitor_id' => $visitor->id,
                'access_method' => 'mobile_button',
                'direction' => 'entry',
                'status' => 'denied',
                'reason' => "Izin Masuk Ditolak Warga ({$user->name}) untuk {$visitor->guest_name}: '{$reason}'",
                'triggered_at' => now(),
            ]);
        }

        ActivityLogger::log(
            'visitor.rejected',
            "Warga {$user->name} menolak izin masuk: {$visitor->guest_name} ({$reason}).",
            'warning',
            ['visitor_id' => $visitor->id, 'reason' => $reason],
            $user,
            $visitor
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Izin masuk untuk {$visitor->guest_name} telah ditolak.",
            ]);
        }

        return redirect()->back()->with('success', "Izin masuk untuk {$visitor->guest_name} telah ditolak.");
    }

    /**
     * Kirim konfirmasi & bukti pembayaran iuran warga ke Bendahara RT.
     */
    public function payBilling(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'billing_id' => 'nullable|exists:billings,id',
            'payment_method' => 'required|in:manual_transfer,qris,cash',
            'amount_paid' => 'required|numeric|min:1000',
            'proof_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ], [
            'amount_paid.min' => 'Nominal pembayaran minimal Rp 1.000.',
            'proof_image.max' => 'Ukuran berkas bukti transfer maksimal 5 MB.',
        ]);

        $tenant = Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first();
        $houseId = $user->house_id ?? House::where('tenant_id', $tenant?->id)->value('id');
        if (!$houseId) {
            return redirect()->back()->with('error', 'Unit rumah Anda belum terdaftar. Lengkapi profil rumah terlebih dahulu.');
        }

        $billing = !empty($validated['billing_id'])
            ? Billing::where('house_id', $houseId)->findOrFail($validated['billing_id'])
            : Billing::firstOrCreate(
                [
                    'tenant_id' => $tenant?->id ?? 1,
                    'house_id' => $houseId,
                    'billing_period' => now()->startOfMonth()->toDateString(),
                ],
                [
                    'amount' => $validated['amount_paid'],
                    'description' => 'Iuran Kebersihan, Keamanan, & Kas RT',
                    'status' => 'unpaid',
                    'due_date' => now()->startOfMonth()->addDays(15)->toDateString(),
                ]
            );

        if ($billing->status === 'paid') {
            return redirect()->back()->with('info', 'Tagihan iuran periode ini sudah berstatus LUNAS.');
        }

        $alreadyPending = Payment::where('billing_id', $billing->id)
            ->whereNull('verified_at')
            ->whereNull('rejection_reason')
            ->exists();

        if ($alreadyPending) {
            return redirect()->back()->with('warning', 'Bukti pembayaran untuk periode ini sudah dikirim dan sedang menunggu verifikasi Bendahara.');
        }

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $fileName = 'pay_' . $user->id . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $proofPath = $file->storeAs('payments', $fileName, 'local');
        }

        $payment = Payment::create([
            'billing_id' => $billing->id,
            'payer_user_id' => $user->id,
            'payment_method' => $validated['payment_method'],
            'proof_image' => $proofPath,
            'amount_paid' => $validated['amount_paid'],
        ]);

        $billing->update(['status' => 'pending_verification']);

        ActivityLogger::log(
            'finance.payment_submitted',
            "Warga {$user->name} mengirim bukti pembayaran iuran Rp " . number_format((float) $validated['amount_paid'], 0, ',', '.') . '.',
            'info',
            ['payment_id' => $payment->id, 'method' => $validated['payment_method']],
            $user,
            $payment
        );

        return redirect()->back()->with('success', 'Konfirmasi & bukti pembayaran iuran berhasil dikirim ke Bendahara RT untuk diverifikasi!');
    }

    /**
     * Halaman E-Kwitansi resmi pembayaran iuran yang telah lunas.
     */
    public function showReceipt(int $billingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $billing = Billing::with(['house', 'tenant', 'payments.payer', 'payments.verifier'])->findOrFail($billingId);
        $isOwner = $user->house_id && (int) $user->house_id === (int) $billing->house_id;
        $isTenantManager = $user->isPengurus() && ($user->isSuperAdmin() || (int) $user->tenant_id === (int) $billing->tenant_id);
        if (!$isOwner && !$isTenantManager) {
            abort(403, 'Anda tidak berwenang mengakses kwitansi unit rumah lain.');
        }

        if ($billing->status !== 'paid') {
            return redirect()->route('warga.index')->with('warning', 'E-Kwitansi hanya tersedia untuk tagihan yang sudah lunas diverifikasi.');
        }

        $tenant = $billing->tenant ?? Tenant::find($billing->tenant_id) ?? Tenant::first();
        $payment = $billing->payments->whereNotNull('verified_at')->sortByDesc('verified_at')->first()
            ?? $billing->payments->sortByDesc('id')->first();

        return view('pages.payment-receipt', compact('user', 'tenant', 'billing', 'payment'));
    }

    /**
     * Menampilkan gambar bukti transfer secara aman untuk pembayar & pengurus kas.
     */
    public function showPaymentProof(int $id)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        $payment = Payment::with('billing')->findOrFail($id);
        $isPayer = (int) $payment->payer_user_id === (int) $currentUser->id;
        $isSameHouse = $currentUser->house_id && $payment->billing && (int) $payment->billing->house_id === (int) $currentUser->house_id;
        $sameTenant = $currentUser->isSuperAdmin() || ($payment->billing && (int) $currentUser->tenant_id === (int) $payment->billing->tenant_id);
        $isFinanceOfficer = $sameTenant && in_array($currentUser->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'bendahara', 'sekretaris', 'pengurus_rt']);

        if (!$isPayer && !$isSameHouse && !$isFinanceOfficer) {
            abort(403, 'Akses ditolak: Anda tidak berwenang melihat bukti pembayaran ini.');
        }

        $proofPath = $payment->proof_image;
        if (!$proofPath) {
            abort(404, 'Bukti pembayaran tidak dilampirkan.');
        }

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($proofPath)) {
            return response()->file(\Illuminate\Support\Facades\Storage::disk('local')->path($proofPath));
        }
        if (file_exists(storage_path('app/' . $proofPath))) {
            return response()->file(storage_path('app/' . $proofPath));
        }
        if (file_exists(public_path($proofPath))) {
            return response()->file(public_path($proofPath));
        }

        abort(404, 'Berkas bukti transfer tidak ditemukan.');
    }
}
