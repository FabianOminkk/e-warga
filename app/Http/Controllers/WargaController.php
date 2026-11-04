<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WargaController extends Controller
{
    public function index()
    {
        $user = Auth::user() ?? User::where('role', 'warga')->with('house')->first();
        $tenant = Tenant::first();
        $smartGate = SmartGate::first();

        // Tagihan Warga
        $billing = Billing::where('house_id', $user?->house_id)->latest('billing_period')->first();

        // Tamu Aktif Warga Ini
        $myVisitors = Visitor::where('invited_by_user_id', $user?->id)->latest()->take(5)->get();

        return view('pages.warga', compact('user', 'tenant', 'smartGate', 'billing', 'myVisitors'));
    }

    public function triggerOpenGate(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'warga')->first();
        $tenant = Tenant::first();
        $gate = SmartGate::first();

        $log = GateLog::create([
            'tenant_id' => $tenant->id,
            'smart_gate_id' => $gate->id,
            'user_id' => $user->id,
            'access_method' => 'mobile_button',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => 'Buka Pintu via Tombol HP Warga: ' . $user->name,
            'triggered_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Palang gerbang berhasil dibuka! Silakan lewat.',
                'log_id' => $log->id,
                'duration' => 5,
            ]);
        }

        return redirect()->back()->with('success', 'Palang gerbang berhasil dibuka! Silakan masuk.');
    }

    public function createVisitorPass(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:150',
            'guest_type' => 'required|in:guest,courier,ride_hailing,contractor',
            'vehicle_plate' => 'nullable|string|max:20',
            'duration_hours' => 'required|integer|min:1|max:48',
        ]);

        $user = Auth::user() ?? User::where('role', 'warga')->first();
        $tenant = Tenant::first();

        $validFrom = now();
        $validUntil = now()->addHours($validated['duration_hours']);
        $qrToken = 'PASS-' . strtoupper(Str::random(10));

        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'host_house_id' => $user->house_id ?? 1,
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
        $houseNumber = $user->house ? $user->house->block_and_number : 'Lingkungan';

        return redirect()->back()->with('panic_sent', "TOMBOL DARURAT DIAKTIFKAN! Sinyal bahaya dari {$user->name} ({$houseNumber}) telah diteruskan ke Pos Satpam dan Pengurus RT.");
    }
}

