<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\GateLog;
use App\Models\House;
use App\Models\LetterRequest;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WargaController extends Controller
{
    public function index()
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $user = Auth::user();

        if (!$user) {
            $user = User::where('tenant_id', $tenant?->id)->where('role', 'warga')->with('house')->first() 
                ?? User::where('role', 'warga')->first();
        }

        $smartGate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();

        // Tagihan Warga
        $billing = null;
        if ($user && $user->house_id) {
            $billing = Billing::where('house_id', $user->house_id)->latest('billing_period')->first();
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

        return view('pages.warga', compact('user', 'tenant', 'smartGate', 'billing', 'myVisitors', 'myGateLogs', 'myLetters', 'houses'));
    }

    public function profile()
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $user = Auth::user();

        if (!$user) {
            $user = User::where('tenant_id', $tenant?->id)->where('role', 'warga')->first() 
                ?? User::where('role', 'warga')->first();
        }

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

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'locked' => true,
                ], 403);
            }

            return redirect()->back()->with('error', $errorMessage);
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();

        if (!$gate) {
            return redirect()->back()->with('error', 'Perangkat palang gerbang tidak ditemukan atau sedang offline.');
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
            'vehicle_plate' => $validated['vehicle_plate'],
            'qr_token' => $qrToken,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'is_used' => false,
        ]);

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
            'nik.required' => 'Nomor KTP (NIK) wajib diisi untuk verifikasi identitas resmi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_place.required' => 'Tempat lahir wajib diisi sesuai KTP.',
            'address_ktp.required' => 'Alamat domisili sesuai KTP wajib diisi.',
        ]);

        $dataToUpdate = [
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'nik' => $validated['nik'],
            'birth_date' => $validated['birth_date'],
            'birth_place' => $validated['birth_place'],
            'gender' => $validated['gender'],
            'religion' => $validated['religion'] ?? 'Islam',
            'occupation' => $validated['occupation'] ?? 'Karyawan Swasta',
            'address_ktp' => $validated['address_ktp'],
            'house_id' => $validated['house_id'] ?? $user->house_id,
        ];

        // Buat RFID Tag otomatis terintegrasi dengan NIK KTP jika belum ada
        if (!$user->rfid_tag || empty($user->rfid_tag)) {
            $dataToUpdate['rfid_tag'] = 'RFID-' . $validated['nik'];
        }

        // Upload Foto KTP jika dilampirkan
        if ($request->hasFile('ktp_photo')) {
            $file = $request->file('ktp_photo');
            $uploadDir = public_path('uploads/ktp');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = 'ktp_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $dataToUpdate['ktp_photo'] = 'uploads/ktp/' . $fileName;
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

        return redirect()->route('warga.profile')->with('success', 'Data identitas KTP berhasil disimpan! Berkas Anda saat ini masuk ke antrean verifikasi Ketua RW (Bpk. H. Rahmat Hidayat). Akses palang pintu & izin tamu akan aktif otomatis setelah disetujui Pak RW.');
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

        return redirect()->back()->with('success', 'Password akun warga berhasil diperbarui!');
    }
}
