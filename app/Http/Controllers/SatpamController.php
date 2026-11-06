<?php

namespace App\Http\Controllers;

use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SatpamController extends Controller
{
    public function console()
    {
        $user = Auth::user();
        if ($user && $user->role === 'warga') {
            return redirect()->route('warga.index')->with('error', 'Warga tidak memiliki akses ke Konsol Pos Jaga Satpam.');
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $smartGate = SmartGate::where('tenant_id', $tenant->id)->where('status', 'online')->first() 
            ?? SmartGate::where('tenant_id', $tenant->id)->first() 
            ?? SmartGate::first();
        $recentLogs = GateLog::where('tenant_id', $tenant->id)->with(['user'])->latest('triggered_at')->take(10)->get();
        $activeVisitors = Visitor::where('is_used', false)
            ->where('valid_until', '>=', now())
            ->with(['hostHouse', 'invitedBy'])
            ->latest()
            ->take(8)
            ->get();

        return view('pages.satpam', compact('tenant', 'smartGate', 'recentLogs', 'activeVisitors'));
    }

    public function triggerManual(Request $request)
    {
        $reason = $request->input('reason', 'Buka Manual Pos Satpam');
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();

        if (!$gate) {
            return redirect()->back()->with('error', 'Perangkat palang gerbang belum terpasang.');
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

        $satpamUser = Auth::user() ?? User::where('role', 'satpam')->first() ?? User::first();

        $visitor->update([
            'is_used' => true,
            'checked_in_by_satpam_id' => $satpamUser?->id,
            'checked_in_at' => now(),
        ]);

        // Picu palang pintu terbuka
        $gate = SmartGate::first();
        if ($gate) {
            GateLog::create([
                'tenant_id' => $visitor->tenant_id,
                'smart_gate_id' => $gate->id,
                'visitor_id' => $visitor->id,
                'access_method' => 'qr_code',
                'direction' => 'entry',
                'status' => 'granted',
                'reason' => 'Tiket Tamu Terverifikasi: ' . $visitor->guest_name . ' (' . $visitor->guest_type . ')',
                'triggered_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Tiket VALID! Tamu: ' . $visitor->guest_name . ' diizinkan masuk. Palang terbuka.');
    }
}
