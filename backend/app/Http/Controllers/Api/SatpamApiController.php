<?php

namespace App\Http\Controllers\Api;

use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
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

        $smartGate = SmartGate::where('tenant_id', $tenant?->id)->where('status', 'online')->first() 
            ?? SmartGate::where('tenant_id', $tenant?->id)->first() 
            ?? SmartGate::first();

        $recentLogs = GateLog::where('tenant_id', $tenant?->id)
            ->with(['user'])
            ->latest('triggered_at')
            ->take(15)
            ->get();

        $activeVisitors = Visitor::where('is_used', false)
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

        $panicAlert = Cache::get("panic_alert_active_{$tenant?->id}");

        return $this->success([
            'tenant'              => $tenant,
            'smart_gate'          => $smartGate,
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
        $gate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();

        if (!$gate) {
            return $this->error('Perangkat palang gerbang tidak ditemukan.', null, 404);
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

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();
        $satpamUser = $request->user() ?? Auth::user();

        $visitor->update([
            'is_used'                 => true,
            'checked_in_at'           => now(),
            'checked_in_by_satpam_id' => $satpamUser?->id,
        ]);

        if ($gate) {
            Cache::put("gate_remote_open_{$gate->id}", [
                'action'        => 'open',
                'requested_by'  => "Tamu: {$visitor->guest_name}",
                'role'          => 'guest',
                'duration_secs' => 5,
                'requested_at'  => now()->toISOString(),
            ], 30);

            GateLog::create([
                'tenant_id'     => $tenant?->id ?? $gate->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id'       => $satpamUser?->id,
                'access_method' => 'qr_code',
                'direction'     => 'entry',
                'status'        => 'granted',
                'reason'        => "Check-in Tamu: {$visitor->guest_name} ({$visitor->guest_type})",
                'triggered_at'  => now(),
            ]);
        }

        return $this->success($visitor, "Tamu {$visitor->guest_name} berhasil check-in. Palang gerbang terbuka.");
    }
}
