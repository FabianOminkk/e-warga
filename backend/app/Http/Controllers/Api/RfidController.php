<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmartGate;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\RfidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RfidController extends Controller
{
    /**
     * Validate X-Device-Key and (on /tap) Authorization: Bearer header against configured device key.
     */
    private function rejectInvalidDeviceKey(Request $request, bool $allowBearer = false): ?JsonResponse
    {
        $expectedKey = (string) config('services.rfid.device_key', 'smartrw-rfid-device');
        $requireKey = (bool) config('services.rfid.require_key', false);
        $providedKey = $request->header('X-Device-Key');
        $bearerToken = $allowBearer ? $request->bearerToken() : null;

        if ($providedKey !== null && $providedKey !== '' && !hash_equals($expectedKey, (string) $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'X-Device-Key tidak valid.',
            ], 401);
        }

        if ($bearerToken !== null && $bearerToken !== '' && !hash_equals($expectedKey, (string) $bearerToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Bearer token tidak valid.',
            ], 401);
        }

        if ($requireKey && empty($providedKey) && empty($bearerToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Header autentikasi perangkat wajib disertakan.',
            ], 401);
        }

        return null;
    }

    /**
     * Menerima data tap kartu RFID / e-KTP dari perangkat fisik ESP32.
     * Route: POST /api/rfid/tap (stateless API, bebas CSRF).
     */
    public function tap(Request $request): JsonResponse
    {
        if ($authError = $this->rejectInvalidDeviceKey($request, true)) {
            return $authError;
        }

        $validated = $request->validate([
            'uid' => 'required|string|max:100',
            'device_id' => 'nullable|string|max:100',
            'tenant_id' => 'nullable|integer',
        ]);

        $rawUid = strtoupper(trim($validated['uid']));
        $normalized = RfidService::normalizeUid($rawUid);
        $cleanUid = $normalized['clean'];
        $formattedTag = $normalized['formatted'];

        $deviceId = trim((string) ($validated['device_id'] ?? $request->header('X-Device-UID', 'esp32-scanner-01')));
        $requestedTenantId = isset($validated['tenant_id']) ? (int) $validated['tenant_id'] : null;

        $gate = RfidService::recordHeartbeat(
            $deviceId,
            $request->ip(),
            $requestedTenantId,
            null,
            'Scanner RFID ESP32 (' . $deviceId . ')'
        );

        $tenantId = $requestedTenantId ?? $gate?->tenant_id ?? RfidService::resolveTenantId() ?? 1;

        Cache::put('rfid_last_tap_global', [
            'uid' => $rawUid,
            'clean_uid' => $cleanUid,
            'formatted_uid' => $formattedTag,
            'device_id' => $deviceId,
            'tapped_at' => now()->toIso8601String(),
        ], 120);

        // 1. Cek sesi pairing e-KTP aktif dari warga yang sedang login di Dashboard Warga
        if ($pairingResponse = $this->handleResidentPairing($tenantId, $gate, $deviceId, $rawUid, $cleanUid, $formattedTag)) {
            return $pairingResponse;
        }

        // 2. Cek sesi pairing dari formulir pendaftaran kilat / registrasi warga baru
        if ($regResponse = $this->handleRegistrationPairing($tenantId, $gate, $deviceId, $rawUid, $cleanUid, $formattedTag)) {
            return $regResponse;
        }

        // 3. Cari warga pemilik kartu untuk verifikasi akses gerbang / portal
        return $this->handleGateAccessScan($tenantId, $gate, $deviceId, $rawUid, $cleanUid, $formattedTag);
    }

    /**
     * Menerima sinyal denyut online (heartbeat) ESP32 setiap 2-3 detik.
     * Route: POST /api/rfid/heartbeat (stateless API, bebas CSRF).
     */
    public function heartbeat(Request $request): JsonResponse
    {
        if ($authError = $this->rejectInvalidDeviceKey($request)) {
            return $authError;
        }

        $validated = $request->validate([
            'device_id' => 'nullable|string|max:100',
            'ip' => 'nullable|string|max:45',
            'tenant_id' => 'nullable|integer',
            'rfid_ver' => 'nullable|string|max:20',
        ]);

        $deviceId = trim((string) ($validated['device_id'] ?? $request->header('X-Device-UID', 'esp32-scanner-01')));
        $ip = $validated['ip'] ?? $request->ip();
        $tenantId = isset($validated['tenant_id']) ? (int) $validated['tenant_id'] : null;

        Cache::put('rfid_reader_online', true, 30);

        RfidService::recordHeartbeat(
            $deviceId,
            $ip,
            $tenantId,
            $validated['rfid_ver'] ?? null,
            'Scanner RFID ESP32 (' . $deviceId . ')'
        );

        return response()->json([
            'status' => 'online',
            'success' => true,
            'device_id' => $deviceId,
            'ip' => $ip,
        ], 200);
    }

    /**
     * Status endpoint cepat untuk mengecek status online dan tap terakhir perangkat RFID.
     * Route: GET /api/rfid/status.
     */
    public function status(): JsonResponse
    {
        $gateStatus = RfidService::getGateOnlineStatus();
        $deviceStatus = RfidService::getDeviceOnlineStatus();

        return response()->json([
            'success' => true,
            'online' => $gateStatus['online'],
            'device_online' => $deviceStatus['online'],
            'status' => $gateStatus['online'] ? 'online' : 'offline',
            'last_seen_seconds_ago' => $gateStatus['seconds_ago'],
            'reader_info' => $gateStatus['reader_info'],
            'last_tap' => Cache::get('rfid_last_tap_global'),
        ], 200);
    }

    /**
     * Menangani penautan kartu saat warga membuka mode Pairing ESP32 di Dashboard Warga.
     */
    private function handleResidentPairing(
        int $tenantId,
        ?SmartGate $gate,
        string $deviceId,
        string $rawUid,
        string $cleanUid,
        string $formattedTag
    ): ?JsonResponse {
        $pairingSession = Cache::get("rfid_pairing_tenant_{$tenantId}")
            ?? Cache::get('rfid_pairing_global');

        if (!$pairingSession || empty($pairingSession['user_id'])) {
            return null;
        }

        $targetUser = User::find($pairingSession['user_id']);
        if (!$targetUser) {
            return null;
        }

        $alreadyUsed = RfidService::findUserByUid($rawUid, $cleanUid, $formattedTag, $targetUser->id);
        if ($alreadyUsed) {
            Cache::put(
                "rfid_pairing_error_{$targetUser->id}",
                "UID {$cleanUid} sudah terdaftar atas nama warga lain ({$alreadyUsed->name}). Gunakan kartu lain.",
                60
            );

            return response()->json([
                'success' => true,
                'paired' => false,
                'mode' => 'pairing_conflict',
                'gate_status' => 'denied',
                'message' => "Kartu berhasil dibaca, tetapi UID {$cleanUid} sudah terdaftar pada {$alreadyUsed->name}",
                'uid' => $rawUid,
                'formatted_uid' => $formattedTag,
                'device_id' => $deviceId,
            ], 200);
        }

        $targetUser->update(['rfid_tag' => $formattedTag]);

        $userTenantId = $targetUser->tenant_id ?? $tenantId;
        Cache::forget("rfid_pairing_tenant_{$tenantId}");
        Cache::forget("rfid_pairing_tenant_{$userTenantId}");
        Cache::forget('rfid_pairing_global');
        Cache::forget("rfid_pairing_error_{$targetUser->id}");
        Cache::put("rfid_pairing_success_{$targetUser->id}", $formattedTag, 120);

        RfidService::logGateEvent(
            $gate,
            $userTenantId,
            'granted',
            "Registrasi Pairing e-KTP Berhasil via {$deviceId}: {$targetUser->name} ({$formattedTag})",
            $targetUser->id
        );

        ActivityLogger::log(
            'rfid.paired_esp32',
            "Warga {$targetUser->name} menautkan e-KTP ({$formattedTag}) melalui perangkat {$deviceId}.",
            'info',
            ['rfid_tag' => $formattedTag, 'device_id' => $deviceId],
            $targetUser
        );

        return response()->json([
            'success' => true,
            'paired' => true,
            'mode' => 'pairing_warga',
            'gate_status' => $targetUser->canAccessGate() ? 'granted' : 'pending_rw',
            'message' => "Kartu berhasil dibaca dan ditautkan ke {$targetUser->name}",
            'uid' => $rawUid,
            'formatted_uid' => $formattedTag,
            'device_id' => $deviceId,
            'resident_name' => $targetUser->name,
        ], 200);
    }

    /**
     * Menangani penautan kartu saat form registrasi warga baru menunggu scan ESP32.
     */
    private function handleRegistrationPairing(
        int $tenantId,
        ?SmartGate $gate,
        string $deviceId,
        string $rawUid,
        string $cleanUid,
        string $formattedTag
    ): ?JsonResponse {
        $regPairing = Cache::get("reg_pairing_tenant_{$tenantId}")
            ?? Cache::get('reg_pairing_global');

        if (!$regPairing) {
            return null;
        }

        $targetTenantId = (int) ($regPairing['tenant_id'] ?? $tenantId);
        $alreadyUsed = RfidService::findUserByUid($rawUid, $cleanUid, $formattedTag);

        if ($alreadyUsed) {
            return response()->json([
                'success' => true,
                'paired' => false,
                'mode' => 'registration_conflict',
                'gate_status' => 'denied',
                'message' => "Kartu berhasil dibaca, tetapi UID {$cleanUid} sudah terdaftar pada {$alreadyUsed->name}",
                'uid' => $rawUid,
                'formatted_uid' => $formattedTag,
                'device_id' => $deviceId,
            ], 200);
        }

        Cache::put("reg_pairing_result_{$targetTenantId}", $formattedTag, 120);
        Cache::put("reg_latest_tap_tenant_{$targetTenantId}", $formattedTag, 120);
        Cache::forget("reg_pairing_tenant_{$targetTenantId}");
        Cache::forget("reg_pairing_tenant_{$tenantId}");
        Cache::forget('reg_pairing_global');

        RfidService::logGateEvent(
            $gate,
            $targetTenantId,
            'granted',
            "Registrasi Kilat e-KTP Berhasil via {$deviceId}: {$formattedTag}",
            null
        );

        return response()->json([
            'success' => true,
            'paired' => true,
            'mode' => 'registration',
            'message' => 'Kartu berhasil dibaca dan dikirim ke formulir pendaftaran',
            'uid' => $rawUid,
            'formatted_uid' => $formattedTag,
            'device_id' => $deviceId,
        ], 200);
    }

    /**
     * Verifikasi akses portal gerbang / identifikasi warga saat tap kartu e-KTP langsung.
     */
    private function handleGateAccessScan(
        int $tenantId,
        ?SmartGate $gate,
        string $deviceId,
        string $rawUid,
        string $cleanUid,
        string $formattedTag
    ): JsonResponse {
        $user = RfidService::findUserByUid($rawUid, $cleanUid, $formattedTag);

        if (!$user) {
            Cache::put("reg_latest_tap_tenant_{$tenantId}", $formattedTag, 120);

            RfidService::logGateEvent(
                $gate,
                $tenantId,
                'denied',
                "Scan RFID ({$rawUid}) via {$deviceId}: Kartu belum terdaftar di sistem E-WARGA",
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'Kartu berhasil dibaca (UID belum terdaftar di akun warga)',
                'uid' => $rawUid,
                'formatted_uid' => $formattedTag,
                'device_id' => $deviceId,
                'mode' => 'unregistered',
                'gate_status' => 'denied',
                'action' => 'denied',
            ], 200);
        }

        $userTenantId = $user->tenant_id ?? $tenantId;
        $houseName = $user->house ? $user->house->block_and_number : 'Warga';

        if (!$user->canAccessGate()) {
            $reason = $user->verification_status === 'pending_rw'
                ? "AKSES DITOLAK: Berkas KTP {$user->name} masih menunggu persetujuan Ketua RW ({$deviceId})"
                : "AKSES DITOLAK: {$user->name} belum melengkapi berkas KTP resmi ({$deviceId})";

            RfidService::logGateEvent($gate, $userTenantId, 'denied', $reason, $user->id);

            return response()->json([
                'success' => true,
                'message' => "Kartu berhasil dibaca ({$user->name}), akses portal menunggu persetujuan Ketua RW",
                'uid' => $rawUid,
                'formatted_uid' => $formattedTag,
                'device_id' => $deviceId,
                'mode' => 'gate_access',
                'gate_status' => 'denied',
                'action' => 'denied',
                'resident_name' => $user->name,
                'house' => $houseName,
            ], 200);
        }

        RfidService::logGateEvent(
            $gate,
            $userTenantId,
            'granted',
            "Tap RFID E-KTP Berhasil via {$deviceId}: {$user->name} ({$houseName})",
            $user->id
        );

        // Teruskan sinyal buka gerbang ke Smart Gate fisik / simulator
        if ($gate) {
            Cache::put("gate_remote_open_{$gate->id}", [
                'user_name' => $user->name,
                'reason' => "Tap RFID E-KTP ({$houseName}) via {$deviceId}",
                'time' => now()->toIso8601String(),
            ], 15);
        }
        Cache::put('gate_remote_open_global', [
            'user_name' => $user->name,
            'reason' => "Tap RFID E-KTP ({$houseName}) via {$deviceId}",
            'time' => now()->toIso8601String(),
        ], 15);

        ActivityLogger::log(
            'gate.rfid_granted',
            "Warga {$user->name} ({$houseName}) membuka portal otomatis via {$deviceId} ({$formattedTag}).",
            'info',
            ['device_id' => $deviceId, 'rfid_tag' => $formattedTag],
            $user
        );

        return response()->json([
            'success' => true,
            'message' => "Kartu berhasil dibaca. Akses portal diizinkan untuk {$user->name}",
            'uid' => $rawUid,
            'formatted_uid' => $formattedTag,
            'device_id' => $deviceId,
            'mode' => 'gate_access',
            'gate_status' => 'granted',
            'action' => 'open',
            'open_duration' => 5,
            'resident_name' => $user->name,
            'house' => $houseName,
        ], 200);
    }
}
