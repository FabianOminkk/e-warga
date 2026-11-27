<?php

namespace App\Http\Controllers\Api;

use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Services\RfidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SatpamApiController extends BaseApiController
{
    /**
     * Get live console data for security guard mobile / tablet app.
     */
    public function console(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        $smartGate = RfidService::resolvePrimaryGate($tenant?->id);
        $isGateOnline = RfidService::getGateOnlineStatus($tenant?->id)['online'];

        $recentLogs = GateLog::where('tenant_id', $tenant?->id)
            ->with(['user'])
            ->latest('triggered_at')
            ->take(15)
            ->get();

        $activeVisitors = Visitor::where('tenant_id', $tenant?->id)
            ->where('is_used', false)
            ->where('valid_until', '>=', now())
            ->with(['hostHouse', 'invitedBy'])
            ->latest()
            ->take(10)
            ->get();

        $verifiedWargaList = User::where('tenant_id', $tenant?->id)
            ->where('role', 'warga')
            ->where('verification_status', 'approved')
            ->with(['house'])
            ->get();

        $tenantId = $tenant?->id ?? 1;
        $panicAlert = Cache::get("panic_alert_active_{$tenantId}");

        return $this->success([
            'tenant'              => $tenant,
            'smart_gate'          => $smartGate,
            'is_gate_online'      => $isGateOnline,
            'panic_alert'         => $panicAlert,
            'recent_logs'         => $recentLogs,
            'active_visitors'     => $activeVisitors,
            'verified_warga_count'=> $verifiedWargaList->count(),
        ], 'Data konsol pos satpam berhasil diambil.');
    }

    /**
     * Security guard triggers manual gate open.
     */
    public function triggerManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:150',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = RfidService::resolvePrimaryGate($tenant?->id);
        $isGateOnline = RfidService::getGateOnlineStatus($tenant?->id)['online'];

        if (!$gate) {
            return $this->error('Perangkat palang gerbang tidak ditemukan.', null, 404);
        }

        if (!$isGateOnline) {
            return $this->error('Smart Gate sedang OFFLINE! Pastikan perangkat ESP32 gerbang terhubung terlebih dahulu.', ['offline' => true], 422);
        }

        $satpamUser = $request->user() ?? Auth::user() ?? User::where('role', 'satpam')->first();
        $reason = $validated['reason'] ?? 'Buka Manual oleh Petugas Satpam';

        Cache::put("gate_remote_open_{$gate->id}", [
            'action'        => 'open',
            'requested_by'  => $satpamUser?->name ?? 'Pos Satpam',
            'role'          => 'satpam',
            'duration_secs' => 5,
            'requested_at'  => now()->toISOString(),
        ], 30);

        $log = GateLog::create([
            'tenant_id'     => $tenant?->id ?? $gate->tenant_id,
            'smart_gate_id' => $gate->id,
            'user_id'       => $satpamUser?->id,
            'access_method' => 'satpam_manual',
            'direction'     => 'entry',
            'status'        => 'granted',
            'reason'        => $reason,
            'triggered_at'  => now(),
        ]);

        return $this->success([
            'log'                   => $log,
            'gate_name'             => $gate->name,
            'hold_duration_seconds' => 5,
        ], 'Palang gerbang berhasil dibuka secara manual oleh petugas jaga.');
    }

    /**
     * Process visitor check-in at security post.
     */
    public function checkInVisitor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visitor_id' => 'nullable|exists:visitors,id',
            'qr_token'   => 'nullable|string',
        ]);

        $visitor = null;
        if (!empty($validated['qr_token'])) {
            $visitor = Visitor::where('qr_token', $validated['qr_token'])->first();
        } elseif (!empty($validated['visitor_id'])) {
            $visitor = Visitor::find($validated['visitor_id']);
        }

        if (!$visitor) {
            return $this->error('Data tamu atau token tiket QR tidak ditemukan.', null, 404);
        }

        if ($visitor->is_used) {
            $usedTime = $visitor->checked_in_at ? $visitor->checked_in_at->format('d/m/Y H:i') : 'sebelumnya';
            return $this->error("Tiket tamu ini sudah pernah digunakan pada {$usedTime}.", null, 400);
        }

        if ($visitor->valid_until && now()->gt($visitor->valid_until)) {
            return $this->error('Tiket tamu ini telah kedaluwarsa.', null, 400);
        }

        if ($visitor->isPending()) {
            return $this->error("Izin masuk untuk {$visitor->guest_name} masih menunggu persetujuan warga pemilik rumah.", null, 403);
        }

        if ($visitor->isRejected()) {
            return $this->error("Izin masuk {$visitor->guest_name} telah DITOLAK oleh warga pemilik rumah.", null, 403);
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = RfidService::resolvePrimaryGate($visitor->tenant_id ?? $tenant?->id);
        $isGateOnline = RfidService::getGateOnlineStatus($visitor->tenant_id ?? $tenant?->id)['online'];
        $satpamUser = $request->user() ?? Auth::user();

        $visitor->update([
            'is_used'                 => true,
            'checked_in_at'           => now(),
            'checked_in_by_satpam_id' => $satpamUser?->id,
        ]);

        if ($gate) {
            if ($isGateOnline) {
                Cache::put("gate_remote_open_{$gate->id}", [
                    'action'        => 'open',
                    'requested_by'  => "Tamu: {$visitor->guest_name}",
                    'role'          => 'guest',
                    'duration_secs' => 5,
                    'requested_at'  => now()->toISOString(),
                ], 30);
                Cache::put('gate_remote_open_global', [
                    'action'        => 'open',
                    'requested_by'  => "Tamu: {$visitor->guest_name}",
                    'role'          => 'guest',
                    'duration_secs' => 5,
                    'requested_at'  => now()->toISOString(),
                ], 30);
            }

            GateLog::create([
                'tenant_id'     => $visitor->tenant_id ?? $tenant?->id ?? $gate->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id'       => $satpamUser?->id,
                'visitor_id'    => $visitor->id,
                'access_method' => 'qr_code',
                'direction'     => 'entry',
                'status'        => 'granted',
                'reason'        => ($isGateOnline ? 'Check-in Tamu: ' : 'Check-in Manual Pos Satpam (ESP32 Offline): ') . "{$visitor->guest_name} ({$visitor->guest_type})",
                'triggered_at'  => now(),
            ]);
        }

        return $this->success([
            'visitor' => $visitor,
            'gate_opened' => $isGateOnline,
        ], $isGateOnline
            ? "Tamu {$visitor->guest_name} berhasil check-in. Palang gerbang terbuka."
            : "Tamu {$visitor->guest_name} dicatat masuk (ESP32 sedang OFFLINE — buka palang secara manual).");
    }
}
