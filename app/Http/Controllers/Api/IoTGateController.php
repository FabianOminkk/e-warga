<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IoTGateController extends Controller
{
    /**
     * Resolves and authenticates the SmartGate device from request headers or body.
     */
    private function resolveDevice(Request $request): SmartGate
    {
        $deviceUid = $request->header('X-Device-UID') 
            ?? $request->input('device_uid');
        $deviceToken = $request->header('X-Device-Token') 
            ?? $request->input('device_token');

        if (!$deviceUid) {
            abort(response()->json([
                'success' => false,
                'message' => 'Missing device UID (X-Device-UID header or device_uid parameter required).',
            ], 401));
        }

        $gate = SmartGate::where('device_uid', $deviceUid)->first();

        if (!$gate) {
            // Dalam mode pengujian otomatis (testing/local), izinkan auto-create jika belum terdaftar
            if (app()->environment('testing', 'local')) {
                $tenant = Tenant::getActiveTenant() ?? Tenant::first();
                $gate = SmartGate::create([
                    'tenant_id' => $tenant?->id ?? 1,
                    'name' => 'Gerbang Otomatis (' . $deviceUid . ')',
                    'device_uid' => $deviceUid,
                    'secret_token' => $deviceToken ?? 'TEST-TOKEN',
                    'status' => 'online',
                    'ip_address' => $request->ip(),
                    'last_ping_at' => now(),
                ]);
            } else {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Device UID tidak terdaftar di sistem.',
                ], 401));
            }
        } else {
            // Verifikasi secret token jika disetel di database
            if ($gate->secret_token && $deviceToken && !hash_equals($gate->secret_token, (string) $deviceToken)) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Invalid device token authentication.',
                ], 401));
            }

            $gate->update([
                'status' => 'online',
                'ip_address' => $request->ip(),
                'last_ping_at' => now(),
            ]);
        }

        return $gate;
    }

    /**
     * Heartbeat Ping from ESP32.
     * ESP32 calls this every 2-5 seconds. Returns whether there is a remote open command.
     */
    public function ping(Request $request): JsonResponse
    {
        $gate = $this->resolveDevice($request);

        // Check if there is a pending remote command (e.g. mobile button or satpam console)
        $remoteCommandKey = "gate_remote_open_{$gate->id}";
        $globalCommandKey = "gate_remote_open_global";

        $command = Cache::pull($remoteCommandKey) ?? Cache::pull($globalCommandKey);

        if ($command) {
            return response()->json([
                'success' => true,
                'status' => 'online',
                'device_uid' => $gate->device_uid,
                'action' => 'open',
                'open_duration' => 5,
                'reason' => $command['reason'] ?? 'Buka via Aplikasi HP',
                'triggered_by' => $command['user_name'] ?? 'Warga Terverifikasi',
                'server_time' => now()->toIso8601String(),
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'online',
            'device_uid' => $gate->device_uid,
            'action' => 'idle',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * RFID Card Tap from MFRC522 sensor connected to ESP32.
     */
    public function scanRfid(Request $request): JsonResponse
    {
        $gate = $this->resolveDevice($request);

        $validated = $request->validate([
            'rfid_uid' => 'required|string',
        ]);

        $rawUid = trim($validated['rfid_uid']);
        // Normalize UID by stripping spaces, hyphens, and colons for flexible hardware match
        $cleanUid = strtoupper(str_replace([' ', ':', '-'], '', $rawUid));

        // Search matching resident by rfid_tag or NIK
        $user = User::where(function ($q) use ($rawUid, $cleanUid) {
            $q->where('rfid_tag', $rawUid)
              ->orWhere('rfid_tag', 'RFID-' . $rawUid)
              ->orWhere('rfid_tag', 'RFID-' . $cleanUid)
              ->orWhere('nik', $rawUid)
              ->orWhere('nik', $cleanUid);
        })->first();

        // 1. KONDISI: KARTU TIDAK TERDAFTAR
        if (!$user) {
            GateLog::create([
                'tenant_id' => $gate->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id' => null,
                'access_method' => 'rfid_card',
                'direction' => 'entry',
                'status' => 'denied',
                'reason' => "AKSES DITOLAK: Kartu RFID ({$rawUid}) Tidak Terdaftar di Sistem E-WARGA",
                'triggered_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'AKSES DITOLAK!',
                'lcd_line2' => 'KARTU TAK DIKENAL',
                'message' => 'Kartu RFID belum terdaftar di sistem lingkungan.',
                'rfid_uid' => $rawUid,
            ], 403);
        }

        // 2. KONDISI: WARGA BELUM DI-ACC KETUA RW
        if (!$user->canAccessGate()) {
            $deniedMessage = $user->verification_status === 'pending_rw'
                ? 'MENUNGGU ACC RW'
                : 'KTP BLM LENGKAP';

            $reason = $user->verification_status === 'pending_rw'
                ? "AKSES DITOLAK: Berkas KTP {$user->name} masih dalam review Ketua RW (Belum di-ACC)"
                : "AKSES DITOLAK: {$user->name} belum melengkapi berkas KTP resmi";

            GateLog::create([
                'tenant_id' => $gate->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id' => $user->id,
                'access_method' => 'rfid_card',
                'direction' => 'entry',
                'status' => 'denied',
                'reason' => $reason,
                'triggered_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'AKSES DITOLAK!',
                'lcd_line2' => $deniedMessage,
                'resident_name' => $user->name,
                'message' => $reason,
            ], 403);
        }

        // 3. KONDISI: AKSES DITERIMA (VERIFIED RW)
        $houseName = $user->house ? $user->house->block_and_number : 'Warga';
        GateLog::create([
            'tenant_id' => $gate->tenant_id,
            'smart_gate_id' => $gate->id,
            'user_id' => $user->id,
            'access_method' => 'rfid_card',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => "Tap RFID E-KTP Berhasil: {$user->name} ({$houseName})",
            'triggered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'granted',
            'action' => 'open',
            'open_duration' => 5, // Durasi servo terangkat (detik)
            'lcd_line1' => 'AKSES DIBERIKAN',
            'lcd_line2' => substr($user->name, 0, 16),
            'resident_name' => $user->name,
            'house' => $houseName,
            'message' => "Selamat datang, {$user->name}! Palang gerbang dibuka.",
        ]);
    }

    /**
     * QR Code Ticket Scan (Visitors / Delivery Couriers).
     */
    public function scanQr(Request $request): JsonResponse
    {
        $gate = $this->resolveDevice($request);

        $validated = $request->validate([
            'qr_token' => 'required|string',
        ]);

        $token = trim($validated['qr_token']);
        $visitor = Visitor::where('qr_token', $token)->first();

        if (!$visitor) {
            GateLog::create([
                'tenant_id' => $gate->tenant_id,
                'smart_gate_id' => $gate->id,
                'access_method' => 'qr_code',
                'direction' => 'entry',
                'status' => 'denied',
                'reason' => "Scan QR Gagal: Tiket ({$token}) tidak ditemukan",
                'triggered_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'TIKET INVALID!',
                'lcd_line2' => 'QR TAK DITEMUKAN',
                'message' => 'Tiket QR Tamu tidak valid.',
            ], 404);
        }

        if ($visitor->is_used) {
            return response()->json([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'TIKET KADALUARSA',
                'lcd_line2' => 'SUDAH DIPAKAI',
                'message' => 'Tiket QR ini sudah pernah digunakan sebelumnya.',
            ], 400);
        }

        if (now()->gt($visitor->valid_until)) {
            return response()->json([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'TIKET EXPIRED!',
                'lcd_line2' => 'WAKTU HABIS',
                'message' => 'Masa berlaku tiket QR tamu telah habis.',
            ], 400);
        }

        // Mark ticket used
        $visitor->update([
            'is_used' => true,
            'checked_in_at' => now(),
        ]);

        GateLog::create([
            'tenant_id' => $visitor->tenant_id,
            'smart_gate_id' => $gate->id,
            'visitor_id' => $visitor->id,
            'access_method' => 'qr_code',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => "Scan QR Tamu Valid: {$visitor->guest_name} ({$visitor->guest_type})",
            'triggered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'granted',
            'action' => 'open',
            'open_duration' => 5,
            'lcd_line1' => 'TAMU TERVERIFIKASI',
            'lcd_line2' => substr($visitor->guest_name, 0, 16),
            'guest_name' => $visitor->guest_name,
            'destination' => $visitor->hostHouse?->block_and_number ?? 'Lingkungan Warga',
            'message' => "Tiket tamu valid untuk {$visitor->guest_name}. Palang dibuka.",
        ]);
    }
}
