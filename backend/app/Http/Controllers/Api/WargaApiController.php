<?php

namespace App\Http\Controllers\Api;

use App\Models\Billing;
use App\Models\GateLog;
use App\Models\LetterRequest;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WargaApiController extends BaseApiController
{
    /**
     * Retrieve complete portal data for the citizen mobile app.
     */
    public function portal(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $smartGate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();

        $billing = null;
        if ($user->house_id) {
            $billing = Billing::where('house_id', $user->house_id)->latest('billing_period')->first();
        }

        $myVisitors = Visitor::where('invited_by_user_id', $user->id)->latest()->take(10)->get();
        $myGateLogs = GateLog::where('user_id', $user->id)->latest('triggered_at')->take(10)->get();
        $myLetters = LetterRequest::where('user_id', $user->id)->latest()->take(10)->get();

        return $this->success([
            'user'         => $user->load(['house', 'vehicles']),
            'tenant'       => $tenant,
            'smart_gate'   => $smartGate,
            'billing'      => $billing,
            'visitors'     => $myVisitors,
            'gate_logs'    => $myGateLogs,
            'letters'      => $myLetters,
        ], 'Data portal warga berhasil diambil.');
    }

    /**
     * Remote trigger open gate from mobile app.
     */
    public function triggerOpenGate(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $smartGate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();

        if (!$smartGate) {
            return $this->error('Gerbang pintar belum terdaftar di sistem.', null, 404);
        }

        // Simpan perintah buka gerbang di Cache untuk dijemput ESP32
        Cache::put("gate_remote_open_{$smartGate->id}", [
            'action'        => 'open',
            'requested_by'  => $user->name,
            'role'          => $user->role,
            'duration_secs' => 5,
            'requested_at'  => now()->toISOString(),
        ], 30);

        $log = GateLog::create([
            'tenant_id'     => $tenant?->id ?? $smartGate->tenant_id,
            'smart_gate_id' => $smartGate->id,
            'user_id'       => $user->id,
            'access_method' => 'mobile_button',
            'direction'     => 'entry',
            'status'        => 'granted',
            'reason'        => "Buka Gerbang Jarak Jauh via Mobile App oleh {$user->name}",
            'triggered_at'  => now(),
        ]);

        return $this->success([
            'log'                   => $log,
            'gate_name'             => $smartGate->name,
            'hold_duration_seconds' => 5,
        ], 'Perintah buka palang gerbang berhasil dikirim ke perangkat IoT.');
    }

    /**
     * Create temporary visitor pass / delivery ticket with QR token.
     */
    public function createVisitor(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $validated = $request->validate([
            'name'           => 'nullable|string|max:100',
            'guest_name'     => 'nullable|string|max:100',
            'guest_type'     => 'nullable|in:guest,courier,ride_hailing,contractor',
            'vehicle_plate'  => 'nullable|string|max:20',
            'purpose'        => 'nullable|string|max:150',
            'valid_hours'    => 'nullable|integer|min:1|max:48',
            'duration_hours' => 'nullable|integer|min:1|max:48',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $hostHouseId = $user->house_id ?? House::where('tenant_id', $tenant?->id)->value('id') ?? House::first()?->id;

        if (!$hostHouseId) {
            return $this->error('Data rumah warga belum terdaftar.', null, 422);
        }

        $guestName = $validated['guest_name'] ?? $validated['name'] ?? 'Tamu Warga';
        $guestType = $validated['guest_type'] ?? 'guest';
        $validHours = (int) ($validated['duration_hours'] ?? $validated['valid_hours'] ?? 12);
        $qrToken = 'PASS-' . strtoupper(Str::random(10));

        $visitor = Visitor::create([
            'tenant_id'          => $tenant?->id ?? 1,
            'host_house_id'      => $hostHouseId,
            'invited_by_user_id' => $user->id,
            'guest_name'         => $guestName,
            'guest_type'         => $guestType,
            'vehicle_plate'      => !empty($validated['vehicle_plate']) ? strtoupper($validated['vehicle_plate']) : null,
            'qr_token'           => $qrToken,
            'valid_from'         => now(),
            'valid_until'        => now()->addHours($validHours),
            'is_used'            => false,
        ]);

        return $this->success($visitor, 'Tiket akses tamu berhasil dibuat.', 201);
    }

    /**
     * Trigger resident panic button broadcast.
     */
    public function panicAlert(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $smartGate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();

        // Broadcast panic alert ke cache
        Cache::put("panic_alert_active_{$tenant->id}", [
            'user_name'    => $user->name,
            'house'        => $user->house ? $user->house->block_and_number : 'Warga',
            'phone_number' => $user->phone_number,
            'triggered_at' => now()->toDateTimeString(),
        ], 300);

        if ($smartGate) {
            GateLog::create([
                'tenant_id'     => $tenant?->id ?? $smartGate->tenant_id,
                'smart_gate_id' => $smartGate->id,
                'user_id'       => $user->id,
                'access_method' => 'mobile_button',
                'direction'     => 'entry',
                'status'        => 'granted',
                'reason'        => "🚨 DARURAT: Tombol Panik Ditekan oleh {$user->name} (" . ($user->house?->block_and_number ?? 'Rumah') . ")",
                'triggered_at'  => now(),
            ]);
        }

        return $this->success([
            'alert_triggered' => true,
            'sender'          => $user->name,
            'timestamp'       => now()->toISOString(),
        ], 'Sinyal darurat (Panic Button) berhasil disiarkan ke Pos Satpam dan Pengurus RT.');
    }

    /**
     * Retrieve billing and payment history for resident house.
     */
    public function bills(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user || !$user->house_id) {
            return $this->success([], 'Tidak ada tagihan untuk akun ini.');
        }

        $billings = Billing::where('house_id', $user->house_id)
            ->with('payments')
            ->orderByDesc('billing_period')
            ->get();

        return $this->success($billings, 'Riwayat tagihan iuran berhasil diambil.');
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $user->id,
            'email'        => 'required|email|max:100|unique:users,email,' . $user->id,
            'gender'       => 'nullable|in:L,P',
            'occupation'   => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        return $this->success($user, 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Kata sandi lama yang Anda masukkan tidak sesuai.', null, 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return $this->success(null, 'Kata sandi berhasil diperbarui.');
    }
}
