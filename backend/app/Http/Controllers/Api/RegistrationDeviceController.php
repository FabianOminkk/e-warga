<?php

namespace App\Http\Controllers\Api;

use App\Models\GateLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RegistrationDeviceController extends BaseApiController
{
    /**
     * Heartbeat / Ping dari Alat ESP32 Meja Registrasi Warga.
     */
    public function ping(Request $request): JsonResponse
    {
        $deviceUid = $request->input('device_uid', 'ESP32-REGIS-DESK-01');
        $tenantId = (int) ($request->input('tenant_id') ?? 1);
        
        // Simpan penanda detak jantung (heartbeat) aktif selama 90 detik
        Cache::put("esp32_heartbeat_tenant_{$tenantId}", now()->timestamp, 90);
        Cache::put("esp32_heartbeat_global", now()->timestamp, 90);
        Cache::put("esp32_device_{$deviceUid}_last_ping", [
            'timestamp' => now()->timestamp,
            'ip' => $request->ip(),
            'time' => now()->toIso8601String(),
        ], 90);

        try {
            \App\Models\SmartGate::updateOrCreate(
                ['tenant_id' => $tenantId, 'device_uid' => $deviceUid],
                [
                    'name' => 'Meja Registrasi Warga',
                    'status' => 'online',
                    'last_ping_at' => now(),
                    'ip_address' => $request->ip(),
                ]
            );
        } catch (\Throwable $e) {}

        return $this->success([
            'device_uid' => $deviceUid,
            'status' => 'online',
            'tenant_id' => $tenantId,
            'mode' => 'registration_desk',
            'server_time' => now()->toIso8601String(),
        ], 'Alat ESP32 Registrasi Warga terhubung online ke server.');
    }

    /**
     * Cek status konektivitas perangkat ESP32 (Online / Offline).
     * Digunakan oleh antarmuka web saat warga ingin melakukan tap kartu e-KTP.
     */
    public function status(Request $request): JsonResponse
    {
        $tenantId = (int) ($request->input('tenant_id') ?? 1);
        $deviceUid = $request->input('device_uid', 'ESP32-REGIS-DESK-01');

        $cachedHeartbeat = Cache::get("esp32_heartbeat_tenant_{$tenantId}")
            ?? Cache::get("esp32_heartbeat_tenant_1")
            ?? Cache::get("esp32_heartbeat_global");
        $isOnline = false;
        $secondsAgo = null;

        if ($cachedHeartbeat) {
            $secondsAgo = now()->timestamp - $cachedHeartbeat;
            if ($secondsAgo <= 60) {
                $isOnline = true;
            }
        }

        if (!$isOnline) {
            $latestGate = \App\Models\SmartGate::whereNotNull('last_ping_at')
                ->latest('last_ping_at')
                ->first();

            if ($latestGate && $latestGate->last_ping_at && $latestGate->last_ping_at->diffInSeconds(now()) <= 60) {
                $isOnline = true;
                $secondsAgo = $latestGate->last_ping_at->diffInSeconds(now());
            }
        }

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'status' => $isOnline ? 'online' : 'offline',
            'status_label' => $isOnline ? 'ESP32 Online (Terhubung ke Hotspot)' : 'ESP32 Offline (Belum Terhubung)',
            'hotspot_ssid' => 'BIAN',
            'last_seen_seconds_ago' => $secondsAgo,
            'device_uid' => $deviceUid,
            'message' => $isOnline
                ? 'Alat ESP32 sudah terhubung ke hotspot ("BIAN"). Sensor siap menerima tap kartu e-KTP.'
                : 'Alat ESP32 belum terhubung ke hotspot. Pastikan modul ESP32 menyala dan hotspot HP ("BIAN") aktif.',
        ]);
    }

    /**
     * Menerima pembacaan kartu e-KTP fisik dari alat ESP32 Registrasi Warga.
     * Mengirimkan UID langsung ke formulir pendaftaran warga (/register) secara real-time.
     */
    public function scan(Request $request): JsonResponse
    {
        $rawUid = $request->input('rfid_uid') ?? $request->input('rfid_tag');
        if (!$rawUid) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'lcd_line1' => 'SCAN ERROR!',
                'lcd_line2' => 'UID TIDAK TERBACA',
                'message' => 'Parameter rfid_uid atau rfid_tag wajib disertakan.',
            ], 400);
        }

        $rawUid = trim($rawUid);
        $cleanUid = strtoupper(str_replace([' ', ':', '-', 'RFID'], '', $rawUid));
        $formattedTag = 'RFID-' . $cleanUid;
        $tenantId = $request->input('tenant_id') ?? 1;

        // 1. Validasi: Pastikan kartu ini belum terdaftar pada warga lain di database
        $alreadyUsed = User::where(function ($q) use ($rawUid, $cleanUid, $formattedTag) {
            $q->where('rfid_tag', $rawUid)
              ->orWhere('rfid_tag', $cleanUid)
              ->orWhere('rfid_tag', $formattedTag)
              ->orWhere('nik', $cleanUid);
        })->first();

        // 2. Cek apakah ada sesi pairing aktif dari warga yang sedang login di web dashboard
        $pairingSession = Cache::get("rfid_pairing_tenant_{$tenantId}");
        if ($pairingSession && !empty($pairingSession['user_id'])) {
            $targetUser = User::find($pairingSession['user_id']);
            if ($targetUser) {
                // Periksa apakah UID ini sudah dipakai warga lain (selain dirinya sendiri)
                if ($alreadyUsed && $alreadyUsed->id !== $targetUser->id) {
                    return response()->json([
                        'success' => false,
                        'status' => 'denied',
                        'lcd_line1' => 'REGIS GAGAL!',
                        'lcd_line2' => 'KARTU SUDAH ADA',
                        'message' => "UID {$cleanUid} sudah terdaftar atas nama warga lain ({$alreadyUsed->name}).",
                    ], 422);
                }

                // Tautkan nomor kartu e-KTP fisik ke profil warga yang login
                $targetUser->update(['rfid_tag' => $formattedTag]);
                Cache::forget("rfid_pairing_tenant_{$tenantId}");
                Cache::put("rfid_pairing_success_{$targetUser->id}", $formattedTag, 120);
                Cache::put("esp32_heartbeat_tenant_{$tenantId}", now()->timestamp, 35);

                $deviceUid = $request->input('device_uid', 'ESP32-REGIS-DESK-01');
                $gate = \App\Models\SmartGate::updateOrCreate(
                    ['tenant_id' => $tenantId, 'device_uid' => $deviceUid],
                    ['name' => 'Meja Registrasi Warga', 'status' => 'online', 'last_ping_at' => now(), 'ip_address' => $request->ip()]
                );

                GateLog::create([
                    'tenant_id' => $tenantId,
                    'smart_gate_id' => $gate->id,
                    'user_id' => $targetUser->id,
                    'access_method' => 'rfid_card',
                    'direction' => 'entry',
                    'status' => 'granted',
                    'reason' => "Registrasi Pairing e-KTP Berhasil via Alat ESP32 Meja: {$targetUser->name} ({$formattedTag})",
                    'triggered_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'paired',
                    'lcd_line1' => 'KTP TERCATAT!',
                    'lcd_line2' => strtoupper(substr($targetUser->name, 0, 16)),
                    'resident_name' => $targetUser->name,
                    'rfid_uid' => $formattedTag,
                    'message' => "UID e-KTP {$formattedTag} berhasil didaftarkan untuk warga {$targetUser->name}.",
                ]);
            }
        }

        if ($alreadyUsed) {
            return response()->json([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'REGIS GAGAL!',
                'lcd_line2' => 'KARTU SUDAH ADA',
                'message' => "UID {$cleanUid} sudah terdaftar atas nama {$alreadyUsed->name}.",
            ], 422);
        }

        // 3. Fallback: Simpan hasil scan ke Cache untuk form registrasi kilat
        Cache::put("reg_pairing_result_{$tenantId}", $formattedTag, 120);
        Cache::put("reg_latest_tap_tenant_{$tenantId}", $formattedTag, 120);
        Cache::put("esp32_heartbeat_tenant_{$tenantId}", now()->timestamp, 35);
        Cache::forget("reg_pairing_tenant_{$tenantId}");

        // 3. Catat di log aktivitas sistem & update detak jantung gerbang
        $deviceUid = $request->input('device_uid', 'ESP32-REGIS-DESK-01');
        $gate = \App\Models\SmartGate::updateOrCreate(
            ['tenant_id' => $tenantId, 'device_uid' => $deviceUid],
            ['name' => 'Meja Registrasi Warga', 'status' => 'online', 'last_ping_at' => now(), 'ip_address' => $request->ip()]
        );

        GateLog::create([
            'tenant_id' => $tenantId,
            'smart_gate_id' => $gate->id,
            'user_id' => null,
            'access_method' => 'rfid_card',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => "Registrasi Kilat e-KTP Berhasil via Alat ESP32 Meja Pendaftaran: {$formattedTag}",
            'triggered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => 'paired',
            'lcd_line1' => 'KTP TERCATAT!',
            'lcd_line2' => 'REGIS KILAT OK',
            'resident_name' => 'Warga Baru',
            'rfid_uid' => $formattedTag,
            'message' => "UID e-KTP {$formattedTag} berhasil dikirim ke formulir registrasi kilat.",
        ]);
    }
}
