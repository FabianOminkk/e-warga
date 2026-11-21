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
        $deviceUid = $request->input('device_uid', 'ESP32-REGIS-01');
        
        return $this->success([
            'device_uid' => $deviceUid,
            'status' => 'online',
            'mode' => 'registration_desk',
            'server_time' => now()->toIso8601String(),
        ], 'Alat ESP32 Registrasi Warga terhubung online ke server.');
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

        if ($alreadyUsed) {
            return response()->json([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'REGIS GAGAL!',
                'lcd_line2' => 'KARTU SUDAH ADA',
                'message' => "UID {$cleanUid} sudah terdaftar atas nama {$alreadyUsed->name}.",
            ], 422);
        }

        // 2. Simpan hasil scan ke Cache untuk form registrasi kilat (/register)
        // Disimpan selama 120 detik agar tidak hangus jika user sedang mengetik
        Cache::put("reg_pairing_result_{$tenantId}", $formattedTag, 120);
        Cache::put("reg_latest_tap_tenant_{$tenantId}", $formattedTag, 120);
        Cache::forget("reg_pairing_tenant_{$tenantId}");

        // 3. Catat di log aktivitas sistem
        $gate = \App\Models\SmartGate::firstOrCreate(
            ['tenant_id' => $tenantId, 'device_uid' => $request->input('device_uid', 'ESP32-REGIS-01')],
            ['name' => 'Meja Registrasi Warga', 'status' => 'online']
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
