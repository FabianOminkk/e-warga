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
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $isOfficial = in_array($currentUser->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'bendahara', 'satpam']);

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

        // Simpan sesi pairing di cache selama 60 detik
        \Illuminate\Support\Facades\Cache::put("rfid_pairing_tenant_{$tenantId}", [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'started_at' => now()->timestamp,
        ], 60);

        // Hapus penanda sukses sebelumnya jika ada
        \Illuminate\Support\Facades\Cache::forget("rfid_pairing_success_{$user->id}");

        return response()->json([
            'success' => true,
            'expires_in' => 60,
            'message' => 'Mode pairing aktif! Silakan tempelkan fisik kartu e-KTP Anda ke sensor scanner ESP32 di gerbang atau pos satpam dalam waktu 60 detik.',
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
        $pairingSuccess = \Illuminate\Support\Facades\Cache::pull("rfid_pairing_success_{$user->id}");

        if ($pairingSuccess) {
            $user->refresh();
            return response()->json([
                'success' => true,
                'paired' => true,
                'rfid_tag' => $pairingSuccess,
                'message' => "Fisik e-KTP berhasil dideteksi di alat scanner ESP32! Kartu {$pairingSuccess} kini resmi terdaftar untuk akun Anda.",
                'can_access_gate' => $user->canAccessGate(),
                'status_label' => $user->canAccessGate() ? 'Akses Gerbang Aktif' : 'Menunggu Konfirmasi Admin / Pengurus RT/RW',
            ]);
        }

        $isWaiting = \Illuminate\Support\Facades\Cache::has("rfid_pairing_tenant_{$tenantId}");

        return response()->json([
            'success' => true,
            'paired' => false,
            'waiting' => $isWaiting,
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
            \Illuminate\Support\Facades\Cache::forget("rfid_pairing_tenant_{$tenantId}");
        }

        return response()->json(['success' => true, 'message' => 'Sesi pairing dibatalkan.']);
    }
}
