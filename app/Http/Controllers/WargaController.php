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
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $user = Auth::user();
        if (!$user) {
            $user = User::where('tenant_id', $tenant->id)->where('role', 'warga')->with('house')->first() ?? User::where('role', 'warga')->first();
        }
        $smartGate = SmartGate::where('tenant_id', $tenant->id)->first() ?? SmartGate::first();

        // Tagihan Warga
        $billing = null;
        if ($user && $user->house_id) {
            $billing = Billing::where('house_id', $user->house_id)->latest('billing_period')->first();
        }

        // Tamu Aktif Warga Ini
        $myVisitors = $user 
            ? Visitor::where('invited_by_user_id', $user->id)->latest()->take(5)->get()
            : collect();

        return view('pages.warga', compact('user', 'tenant', 'smartGate', 'billing', 'myVisitors'));
    }

    public function triggerOpenGate(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'warga')->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Silakan login terlebih dahulu.');
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
        if (!$user) {
            return redirect()->back()->with('error', 'Sesi tidak valid.');
        }

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

        $houseNumber = $user->house ? $user->house->block_and_number : 'Lingkungan Warga';

        return redirect()->back()->with('panic_sent', "TOMBOL DARURAT DIAKTIFKAN! Sinyal bahaya dari {$user->name} ({$houseNumber}) telah diteruskan ke Pos Satpam dan Pengurus RT.");
    }
}
