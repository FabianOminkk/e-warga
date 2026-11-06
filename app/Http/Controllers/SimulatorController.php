<?php

namespace App\Http\Controllers;

use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class SimulatorController extends Controller
{
    private function getOrCreateDefaultGate()
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        if (!$tenant) {
            $tenant = Tenant::create([
                'name' => 'Cluster Melati Mas',
                'slug' => 'cluster-melati-mas',
                'rt_rw' => 'RT 05 / RW 12',
                'is_active' => true,
            ]);
        }

        $gate = SmartGate::first();
        if (!$gate) {
            $gate = SmartGate::create([
                'tenant_id' => $tenant->id,
                'name' => 'Gerbang Utama Pos Timur',
                'device_uid' => 'ESP32-GATE-DEFAULT',
                'status' => 'online',
                'last_ping_at' => now(),
            ]);
        }

        return [$tenant, $gate];
    }

    public function index()
    {
        [$tenant, $smartGate] = $this->getOrCreateDefaultGate();
        $recentLogs = GateLog::with(['user'])->latest('triggered_at')->take(10)->get();
        $wargaUsers = User::where('role', 'warga')->with('house')->get();

        return view('pages.simulator', compact('tenant', 'smartGate', 'recentLogs', 'wargaUsers'));
    }

    public function triggerSimulation(Request $request)
    {
        [$tenant, $smartGate] = $this->getOrCreateDefaultGate();
        $method = $request->input('method', 'mobile_button');
        $userId = $request->input('user_id');

        $user = $userId ? User::find($userId) : User::where('role', 'warga')->first();

        // VALIDASI KEAMANAN: Warga yang belum di-ACC oleh Pak RW ditolak oleh palang otomatis!
        if ($user && in_array($method, ['mobile_button', 'rfid_card']) && !$user->canAccessGate()) {
            $deniedReason = $user->verification_status === 'pending_rw'
                ? "AKSES DITOLAK: E-KTP/RFID {$user->name} masih dalam review Ketua RW (Belum di-ACC)"
                : "AKSES DITOLAK: {$user->name} belum melengkapi berkas KTP (Akun Terkunci)";

            $log = GateLog::create([
                'tenant_id' => $tenant->id,
                'smart_gate_id' => $smartGate->id,
                'user_id' => $user->id,
                'access_method' => $method,
                'direction' => 'entry',
                'status' => 'denied',
                'reason' => $deniedReason,
                'triggered_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AKSES DITOLAK! ' . $deniedReason . '. Palang gerbang tetap tertutup!',
                'log' => [
                    'id' => $log->id,
                    'user_name' => $user->name,
                    'house' => $user->house?->block_and_number ?? 'Warga Terkunci',
                    'reason' => $deniedReason,
                    'method' => $method,
                    'status' => 'denied',
                    'time' => $log->triggered_at->format('H:i:s'),
                ],
            ], 403);
        }

        $reason = match ($method) {
            'mobile_button' => 'Simulasi Tombol HP: ' . ($user ? $user->name . ' (E-KTP Terverifikasi RW)' : 'Warga'),
            'rfid_card' => 'Simulasi Tap RFID E-KTP: ' . ($user ? $user->name . ' (' . ($user->rfid_tag ?? 'UID-KTP') . ')' : 'Warga'),
            'satpam_manual' => 'Simulasi Buka Manual oleh Pos Satpam',
            'qr_code' => 'Simulasi Scan QR Tiket Tamu/Kurir',
            default => 'Simulasi Akses Pintu',
        };

        $log = GateLog::create([
            'tenant_id' => $tenant->id,
            'smart_gate_id' => $smartGate->id,
            'user_id' => $user?->id,
            'access_method' => $method,
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => $reason,
            'triggered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sinyal terkirim ke palang pintu! Palang terangkat.',
            'log' => [
                'id' => $log->id,
                'user_name' => $user?->name ?? 'Tamu / Umum',
                'house' => $user?->house?->block_and_number ?? 'Pos Jaga',
                'reason' => $reason,
                'method' => $method,
                'time' => $log->triggered_at->format('H:i:s'),
            ],
            'hold_duration_seconds' => 5,
        ]);
    }

    public function getLatestLogs()
    {
        $logs = GateLog::with(['user.house'])
            ->latest('triggered_at')
            ->take(8)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_name' => $log->user?->name ?? 'Tamu',
                    'house' => $log->user?->house?->block_and_number ?? 'Tamu Pos',
                    'method' => $log->access_method,
                    'reason' => $log->reason,
                    'time' => $log->triggered_at->format('H:i:s'),
                ];
            });

        return response()->json(['logs' => $logs]);
    }
}
