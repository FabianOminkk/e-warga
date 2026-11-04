<?php

namespace App\Http\Controllers;

use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SatpamController extends Controller
{
    public function console()
    {
        $tenant = Tenant::first();
        $smartGate = SmartGate::where('status', 'online')->first() ?? SmartGate::first();
        $recentLogs = GateLog::with(['user'])->latest('triggered_at')->take(10)->get();
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
        $tenant = Tenant::first();
        $gate = SmartGate::first();

        $log = GateLog::create([
            'tenant_id' => $tenant->id,
            'smart_gate_id' => $gate->id,
            'user_id' => Auth::id() ?? 5, // Satpam ID
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
        $qrToken = trim($request->input('qr_token'));
        $visitor = Visitor::where('qr_token', $qrToken)->first();

        if (!$visitor) {
            return redirect()->back()->with('error', 'Tiket QR Tamu tidak valid atau tidak ditemukan di sistem.');
        }

        if ($visitor->is_used) {
            return redirect()->back()->with('error', 'Tiket QR Tamu ini sudah pernah digunakan pada ' . $visitor->checked_in_at->format('d/m/Y H:i'));
        }

        if (now()->gt($visitor->valid_until)) {
            return redirect()->back()->with('error', 'Masa berlaku tiket QR Tamu sudah kadaluarsa.');
        }

        $visitor->update([
            'is_used' => true,
            'checked_in_by_satpam_id' => Auth::id() ?? 5,
            'checked_in_at' => now(),
        ]);

        // Picu palang pintu terbuka
        $gate = SmartGate::first();
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

        return redirect()->back()->with('success', 'Tiket VALID! Tamu: ' . $visitor->guest_name . ' diizinkan masuk. Palang terbuka.');
    }
}

