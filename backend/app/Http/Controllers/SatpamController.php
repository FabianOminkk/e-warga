<?php

namespace App\Http\Controllers;

use App\Models\GateLog;
use App\Models\House;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Services\ActivityLogger;
use App\Services\RfidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SatpamController extends Controller
{
    public function console()
    {
        $user = Auth::user();
        if ($user && $user->role === 'warga') {
            return redirect()->route('warga.index')->with('error', 'Warga tidak memiliki akses ke Konsol Pos Jaga Satpam.');
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $smartGate = RfidService::resolvePrimaryGate($tenant?->id);
        $recentLogs = GateLog::where('tenant_id', $tenant?->id)->with(['user'])->latest('triggered_at')->take(10)->get();
        $activeVisitors = Visitor::where('tenant_id', $tenant?->id)
            ->where('is_used', false)
            ->where('approval_status', '!=', 'rejected')
            ->where('valid_until', '>=', now())
            ->with(['hostHouse', 'invitedBy'])
            ->latest()
            ->take(8)
            ->get();

        // Whitelist Warga & E-KTP / RFID yang Di-ACC oleh Pak RW
        $verifiedWargaList = User::where('tenant_id', $tenant?->id)
            ->where('role', 'warga')
            ->with(['house', 'verifiedBy'])
            ->orderByRaw("CASE WHEN verification_status = 'approved' THEN 1 WHEN verification_status = 'pending_rw' THEN 2 ELSE 3 END")
            ->get();

        // Daftar Rumah beserta penghuni untuk Pendaftaran Tamu/Kurir Datang
        $houses = House::where('tenant_id', $tenant?->id)
            ->with(['residents' => fn($q) => $q->where('role', 'warga')->orderByDesc('is_family_head')])
            ->orderBy('block_name')
            ->orderBy('house_number')
            ->get();

        $isGateOnline = RfidService::getGateOnlineStatus($tenant?->id)['online'];

        return view('pages.satpam', compact('tenant', 'smartGate', 'recentLogs', 'activeVisitors', 'verifiedWargaList', 'houses', 'isGateOnline'));
    }

    public function registerVisitor(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:150',
            'guest_type' => 'required|in:guest,courier,ride_hailing,contractor',
            'host_house_id' => 'required|exists:houses,id',
            'vehicle_plate' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:255',
        ]);

        $satpamUser = Auth::user() ?? User::where('role', 'satpam')->first() ?? User::first();
        $house = House::find($validated['host_house_id']);
        $tenantId = $house?->tenant_id ?? (Tenant::getActiveTenant() ?? Tenant::first())?->id;

        // Cari warga pemilik rumah tujuan
        $hostUser = User::where('house_id', $validated['host_house_id'])->where('role', 'warga')->orderByDesc('is_family_head')->first()
            ?? $satpamUser;

        if (!$hostUser || !$tenantId) {
            return redirect()->back()->with('error', 'Data rumah atau lingkungan belum lengkap.');
        }

        $qrToken = 'GUEST-' . strtoupper(\Illuminate\Support\Str::random(10));

        $visitor = Visitor::create([
            'tenant_id' => $tenantId,
            'host_house_id' => $validated['host_house_id'],
            'invited_by_user_id' => $hostUser->id,
            'guest_name' => $validated['guest_name'],
            'guest_type' => $validated['guest_type'],
            'approval_status' => 'pending', // Menunggu persetujuan warga pemilik rumah
            'approval_notes' => 'Tamu/Kurir tiba di Pos Satpam. Menunggu konfirmasi pemilik rumah.',
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'qr_token' => $qrToken,
            'valid_from' => now(),
            'valid_until' => now()->addHours(3),
            'is_used' => false,
        ]);

        ActivityLogger::log(
            'visitor.registered_at_gate',
            "Petugas Satpam mencatat kedatangan {$validated['guest_name']} ({$visitor->guest_type_label}) menuju unit rumah.",
            'info',
            ['visitor_id' => $visitor->id, 'house_id' => $validated['host_house_id']],
            $satpamUser
        );

        return redirect()->back()->with('success', "Permintaan izin masuk untuk {$validated['guest_name']} berhasil dicatat! Menunggu persetujuan pemilik rumah.");
    }

    public function triggerManual(Request $request)
    {
        $reason = $request->input('reason', 'Buka Manual Pos Satpam');
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = RfidService::resolvePrimaryGate($tenant?->id);
        $isGateOnline = RfidService::getGateOnlineStatus($tenant?->id)['online'];

        if (!$gate) {
            $errMsg = 'Perangkat palang gerbang belum terpasang.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'offline' => true, 'message' => $errMsg], 404)
                : redirect()->back()->with('error', $errMsg);
        }

        if (!$isGateOnline) {
            $satpamUser = Auth::user() ?? User::where('role', 'satpam')->first();
            GateLog::create([
                'tenant_id' => $tenant?->id ?? $gate->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id' => $satpamUser?->id,
                'access_method' => 'satpam_manual',
                'direction' => 'entry',
                'status' => 'denied',
                'reason' => "GAGAL BUKA MANUAL: Smart Gate ESP32 sedang OFFLINE ({$reason})",
                'triggered_at' => now(),
            ]);

            $offlineMsg = 'Smart Gate sedang OFFLINE! Pastikan perangkat ESP32 gerbang terhubung ke hotspot/WiFi terlebih dahulu.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'offline' => true, 'message' => $offlineMsg], 422)
                : redirect()->back()->with('error', $offlineMsg);
        }

        $satpamUser = Auth::user() ?? User::where('role', 'satpam')->first() ?? User::first();

        $log = GateLog::create([
            'tenant_id' => $tenant?->id ?? $gate->tenant_id,
            'smart_gate_id' => $gate->id,
            'user_id' => $satpamUser?->id,
            'access_method' => 'satpam_manual',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => $reason,
            'triggered_at' => now(),
        ]);

        // Kirim sinyal pemicu instan ke perangkat IoT fisik ESP32
        \Illuminate\Support\Facades\Cache::put("gate_remote_open_{$gate->id}", [
            'user_name' => 'Petugas Satpam',
            'reason' => $reason,
            'time' => now()->toIso8601String(),
        ], 15);
        \Illuminate\Support\Facades\Cache::put("gate_remote_open_global", [
            'user_name' => 'Petugas Satpam',
            'reason' => $reason,
            'time' => now()->toIso8601String(),
        ], 15);

        ActivityLogger::log(
            'gate.manual_trigger',
            "Petugas Satpam (" . ($satpamUser?->name ?? 'Regu Jaga') . ") membuka palang gerbang secara manual: '{$reason}'.",
            'warning',
            ['reason' => $reason, 'gate_id' => $gate->id],
            $satpamUser
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Palang gerbang dibuka oleh satpam: ' . $reason,
                'log' => $log,
            ]);
        }

        return redirect()->back()->with('success', 'Palang gerbang berhasil dibuka secara manual.');
    }

    public function checkInVisitor(Request $request)
    {
        $qrToken = trim($request->input('qr_token', ''));
        if (empty($qrToken)) {
            return redirect()->back()->with('error', 'Harap masukkan kode QR tiket tamu.');
        }

        $visitor = Visitor::where('qr_token', $qrToken)->first();

        if (!$visitor) {
            return redirect()->back()->with('error', 'Tiket QR Tamu tidak valid atau tidak ditemukan di sistem.');
        }

        if ($visitor->is_used) {
            $usedTime = $visitor->checked_in_at ? $visitor->checked_in_at->format('d/m/Y H:i') : 'sebelumnya';
            return redirect()->back()->with('error', "Tiket QR Tamu ini sudah pernah digunakan pada {$usedTime}.");
        }

        if (now()->gt($visitor->valid_until)) {
            return redirect()->back()->with('error', 'Masa berlaku tiket QR Tamu sudah kadaluarsa.');
        }

        $gate = SmartGate::where('tenant_id', $visitor->tenant_id)->first() ?? SmartGate::first();

        if ($visitor->isPending()) {
            RfidService::logGateEvent(
                $gate,
                $visitor->tenant_id,
                'denied',
                "Check-In Tertahan: Tamu/Kurir {$visitor->guest_name} menunggu persetujuan pemilik rumah",
                null,
                $visitor->id,
                'qr_code'
            );

            return redirect()->back()->with('error', "Izin masuk untuk {$visitor->guest_name} masih menunggu persetujuan warga pemilik rumah.");
        }

        if ($visitor->isRejected()) {
            RfidService::logGateEvent(
                $gate,
                $visitor->tenant_id,
                'denied',
                "Check-In Ditolak: Kunjungan {$visitor->guest_name} ditolak oleh pemilik rumah",
                null,
                $visitor->id,
                'qr_code'
            );

            return redirect()->back()->with('error', "Izin masuk {$visitor->guest_name} telah DITOLAK oleh warga pemilik rumah.");
        }

        $satpamUser = Auth::user() ?? User::where('role', 'satpam')->first() ?? User::first();

        $visitor->update([
            'is_used' => true,
            'checked_in_by_satpam_id' => $satpamUser?->id,
            'checked_in_at' => now(),
        ]);

        // Picu palang pintu terbuka otomatis hanya jika ESP32 terhubung online
        $isGateOnline = RfidService::getGateOnlineStatus($visitor->tenant_id)['online'];
        $gateTriggered = false;

        if ($gate) {
            GateLog::create([
                'tenant_id' => $visitor->tenant_id,
                'smart_gate_id' => $gate->id,
                'visitor_id' => $visitor->id,
                'access_method' => 'qr_code',
                'direction' => 'entry',
                'status' => 'granted',
                'reason' => ($isGateOnline ? 'Tiket Tamu Terverifikasi (Palang ESP32): ' : 'Check-In Manual Pos Satpam (ESP32 Offline): ') . $visitor->guest_name . ' (' . $visitor->guest_type . ')',
                'triggered_at' => now(),
            ]);

            if ($isGateOnline) {
                Cache::put("gate_remote_open_{$gate->id}", [
                    'user_name' => $visitor->guest_name,
                    'reason' => 'Tiket QR Tamu Valid: ' . $visitor->guest_name,
                    'time' => now()->toIso8601String(),
                ], 15);
                Cache::put('gate_remote_open_global', [
                    'user_name' => $visitor->guest_name,
                    'reason' => 'Tiket QR Tamu Valid: ' . $visitor->guest_name,
                    'time' => now()->toIso8601String(),
                ], 15);
                $gateTriggered = true;
            }
        }

        ActivityLogger::log(
            'visitor.checked_in',
            "Tamu terverifikasi: {$visitor->guest_name} ({$visitor->guest_type}) masuk melalui pos satpam.",
            'info',
            ['guest_name' => $visitor->guest_name, 'visitor_id' => $visitor->id, 'gate_online' => $isGateOnline],
            $satpamUser,
            $visitor
        );

        $checkinMsg = $gateTriggered
            ? 'Tiket VALID! Tamu: ' . $visitor->guest_name . ' diizinkan masuk. Palang ESP32 terbuka otomatis.'
            : 'Tiket VALID! Tamu: ' . $visitor->guest_name . ' dicatat masuk. (Catatan: Smart Gate ESP32 sedang OFFLINE — buka palang secara manual).';

        return redirect()->back()->with('success', $checkinMsg);
    }
}
