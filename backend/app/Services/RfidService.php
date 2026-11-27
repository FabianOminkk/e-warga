<?php

namespace App\Services;

use App\Models\GateLog;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RfidService
{
    public const ONLINE_TTL_SECONDS = 30;
    public const DESK_DEVICE_UID = 'ESP32-REGIS-DESK-01';
    /**
     * Normalize raw RFID/e-KTP UID strings into canonical representations.
     *
     * @return array{raw: string, clean: string, formatted: string}
     */
    public static function normalizeUid(string $rawUid): array
    {
        $raw = trim($rawUid);
        $clean = strtoupper(str_replace([' ', ':', '-', 'RFID'], '', $raw));

        return [
            'raw' => $raw,
            'clean' => $clean,
            'formatted' => 'RFID-' . $clean,
        ];
    }

    /**
     * Find a user matching any supported RFID tag format or NIK.
     */
    public static function findUserByUid(
        string $rawUid,
        ?string $cleanUid = null,
        ?string $formattedTag = null,
        ?int $excludeUserId = null
    ): ?User {
        if ($cleanUid === null || $formattedTag === null) {
            $normalized = self::normalizeUid($rawUid);
            $rawUid = $normalized['raw'];
            $cleanUid = $normalized['clean'];
            $formattedTag = $normalized['formatted'];
        }

        $query = User::query();

        if ($excludeUserId !== null) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->where(function ($q) use ($rawUid, $cleanUid, $formattedTag) {
            $q->where('rfid_tag', $rawUid)
                ->orWhere('rfid_tag', $cleanUid)
                ->orWhere('rfid_tag', $formattedTag)
                ->orWhere('rfid_tag', 'RFID-' . $rawUid)
                ->orWhere('nik', $rawUid)
                ->orWhere('nik', $cleanUid);
        })->first();
    }

    /**
     * Resolve tenant ID from explicit input, active tenant, or first tenant row.
     */
    public static function resolveTenantId(?int $tenantId = null): ?int
    {
        if ($tenantId !== null && $tenantId > 0) {
            return $tenantId;
        }

        return Tenant::getActiveTenant()?->id ?? Tenant::query()->value('id');
    }

    /**
     * Persist device heartbeat into both shared RFID cache and SmartGate records.
     */
    public static function recordHeartbeat(
        string $deviceId,
        ?string $ip = null,
        ?int $tenantId = null,
        ?string $rfidVer = null,
        ?string $gateName = null
    ): ?SmartGate {
        $nowTimestamp = now()->timestamp;
        $resolvedTenantId = self::resolveTenantId($tenantId);
        $ttl = self::ONLINE_TTL_SECONDS;
        $isDeskDevice = strcasecmp(trim($deviceId), self::DESK_DEVICE_UID) === 0;

        if (!$isDeskDevice) {
            Cache::put('rfid_reader_online', true, $ttl);
            Cache::put('rfid_reader_info', [
                'device_id' => $deviceId,
                'ip' => $ip,
                'tenant_id' => $resolvedTenantId,
                'timestamp' => $nowTimestamp,
                'last_seen_at' => now()->toIso8601String(),
            ], $ttl);

            if ($resolvedTenantId !== null) {
                Cache::put("esp32_gate_heartbeat_tenant_{$resolvedTenantId}", $nowTimestamp, $ttl + 15);
            }
            Cache::put('esp32_gate_heartbeat_global', $nowTimestamp, $ttl + 15);
        }

        if ($resolvedTenantId !== null) {
            Cache::put("esp32_heartbeat_tenant_{$resolvedTenantId}", $nowTimestamp, $ttl + 15);
        }
        Cache::put('esp32_heartbeat_global', $nowTimestamp, $ttl + 15);

        if ($rfidVer !== null && $rfidVer !== '') {
            if ($resolvedTenantId !== null) {
                Cache::put("esp32_rfid_ver_tenant_{$resolvedTenantId}", $rfidVer, $ttl + 30);
            }
            Cache::put('esp32_rfid_ver_global', $rfidVer, $ttl + 30);
        }

        Cache::put("esp32_device_{$deviceId}_last_ping", [
            'timestamp' => $nowTimestamp,
            'ip' => $ip,
            'time' => now()->toIso8601String(),
            'rfid_ver' => $rfidVer,
        ], $ttl + 30);

        if ($resolvedTenantId === null) {
            return null;
        }

        try {
            $existing = SmartGate::where('device_uid', $deviceId)->first();
            if ($existing) {
                $existing->update([
                    'tenant_id' => $tenantId ?? $existing->tenant_id ?? $resolvedTenantId,
                    'status' => 'online',
                    'last_ping_at' => now(),
                    'ip_address' => $ip ?? $existing->ip_address,
                ]);
                return $existing->fresh();
            }

            return SmartGate::create([
                'device_uid' => $deviceId,
                'tenant_id' => $resolvedTenantId,
                'name' => $gateName ?? ('Scanner RFID ESP32 (' . $deviceId . ')'),
                'status' => 'online',
                'last_ping_at' => now(),
                'ip_address' => $ip,
            ]);
        } catch (\Throwable $e) {
            return SmartGate::where('device_uid', $deviceId)->first();
        }
    }

    /**
     * Synchronize stale SmartGate DB rows back to offline once heartbeats expire.
     */
    public static function syncStaleGateStatuses(int $ttlSeconds = self::ONLINE_TTL_SECONDS): void
    {
        try {
            $cutoff = now()->subSeconds($ttlSeconds);
            $futureLimit = now()->addSeconds(15);
            SmartGate::where('status', 'online')
                ->where(function ($q) use ($cutoff, $futureLimit) {
                    $q->whereNull('last_ping_at')
                        ->orWhere('last_ping_at', '<', $cutoff)
                        ->orWhere('last_ping_at', '>', $futureLimit);
                })
                ->update(['status' => 'offline']);
        } catch (\Throwable $e) {
            // Ignore when running before migrations complete
        }
    }

    /**
     * Resolve the primary physical Smart Gate record (excluding desk registration scanner unless none else exists).
     */
    public static function resolvePrimaryGate(?int $tenantId = null): ?SmartGate
    {
        self::syncStaleGateStatuses();

        return SmartGate::where('tenant_id', $tenantId)
            ->where('device_uid', '!=', self::DESK_DEVICE_UID)
            ->where('status', 'online')
            ->first()
            ?? SmartGate::where('tenant_id', $tenantId)
                ->where('device_uid', '!=', self::DESK_DEVICE_UID)
                ->first()
            ?? SmartGate::where('device_uid', '!=', self::DESK_DEVICE_UID)
                ->where('status', 'online')
                ->first()
            ?? SmartGate::where('device_uid', '!=', self::DESK_DEVICE_UID)
                ->first()
            ?? SmartGate::first();
    }

    /**
     * Check whether the physical Smart Gate / barrier controller ESP32 is connected online.
     *
     * @return array{online: bool, seconds_ago: ?int, reader_info: mixed}
     */
    public static function getGateOnlineStatus(
        ?int $tenantId = null,
        int $ttlSeconds = self::ONLINE_TTL_SECONDS,
        bool $tenantScopedOnly = false
    ): array {
        return self::getDeviceOnlineStatus($tenantId, $ttlSeconds, $tenantScopedOnly, true);
    }

    /**
     * Check whether an ESP32 scanner / gate device is currently connected online.
     *
     * @return array{online: bool, seconds_ago: ?int, reader_info: mixed}
     */
    public static function getDeviceOnlineStatus(
        ?int $tenantId = null,
        int $ttlSeconds = self::ONLINE_TTL_SECONDS,
        bool $tenantScopedOnly = false,
        bool $gateOnly = false
    ): array {
        self::syncStaleGateStatuses($ttlSeconds);

        $resolvedTenantId = $tenantId ?? 1;
        $readerInfo = Cache::get('rfid_reader_info');

        if (Cache::has('rfid_reader_online')) {
            $timestampOk = !is_array($readerInfo) || !isset($readerInfo['timestamp'])
                || ((now()->timestamp - (int) $readerInfo['timestamp']) <= $ttlSeconds);
            $tenantMatch = !$tenantScopedOnly || $tenantId === null
                || (is_array($readerInfo) && (int) ($readerInfo['tenant_id'] ?? 0) === $tenantId);
            $deviceOk = !$gateOnly || !is_array($readerInfo)
                || strcasecmp((string) ($readerInfo['device_id'] ?? ''), self::DESK_DEVICE_UID) !== 0;

            if ($timestampOk && $tenantMatch && $deviceOk) {
                $secondsAgo = is_array($readerInfo) && isset($readerInfo['timestamp'])
                    ? max(0, now()->timestamp - (int) $readerInfo['timestamp'])
                    : 0;

                return [
                    'online' => true,
                    'seconds_ago' => $secondsAgo,
                    'reader_info' => $readerInfo,
                ];
            }
        }

        $heartbeatPrefix = $gateOnly ? 'esp32_gate_heartbeat' : 'esp32_heartbeat';
        $cachedHeartbeat = Cache::get("{$heartbeatPrefix}_tenant_{$resolvedTenantId}");
        if (!$tenantScopedOnly) {
            $cachedHeartbeat = $cachedHeartbeat
                ?? Cache::get("{$heartbeatPrefix}_tenant_1")
                ?? Cache::get("{$heartbeatPrefix}_global");
        }

        if ($cachedHeartbeat) {
            $secondsAgo = now()->timestamp - (int) $cachedHeartbeat;
            if ($secondsAgo >= 0 && $secondsAgo <= $ttlSeconds) {
                return [
                    'online' => true,
                    'seconds_ago' => $secondsAgo,
                    'reader_info' => $readerInfo,
                ];
            }
        }

        $gateQuery = SmartGate::whereNotNull('last_ping_at')
            ->where('status', '!=', 'maintenance')
            ->where('last_ping_at', '>=', now()->subSeconds($ttlSeconds))
            ->where('last_ping_at', '<=', now()->addSeconds(15));

        if ($gateOnly) {
            $gateQuery->where('device_uid', '!=', self::DESK_DEVICE_UID);
        }
        if ($tenantScopedOnly && $tenantId !== null) {
            $gateQuery->where('tenant_id', $tenantId);
        }

        $latestGate = $gateQuery->latest('last_ping_at')->first();
        if ($latestGate && $latestGate->last_ping_at) {
            return [
                'online' => true,
                'seconds_ago' => max(0, (int) $latestGate->last_ping_at->diffInSeconds(now())),
                'reader_info' => $readerInfo ?? [
                    'device_id' => $latestGate->device_uid,
                    'ip' => $latestGate->ip_address,
                    'tenant_id' => $latestGate->tenant_id,
                    'timestamp' => $latestGate->last_ping_at->timestamp,
                    'last_seen_at' => $latestGate->last_ping_at->toIso8601String(),
                ],
            ];
        }

        return [
            'online' => false,
            'seconds_ago' => null,
            'reader_info' => $readerInfo,
        ];
    }

    /**
     * Safely create a GateLog row when a SmartGate record is available.
     */
    public static function logGateEvent(
        ?SmartGate $gate,
        ?int $tenantId,
        string $status,
        string $reason,
        ?int $userId = null,
        ?int $visitorId = null,
        string $method = 'rfid_card'
    ): void {
        if ($gate === null) {
            $resolvedTenantId = self::resolveTenantId($tenantId);
            if ($resolvedTenantId === null) {
                return;
            }
            $gate = SmartGate::where('tenant_id', $resolvedTenantId)->first() ?? SmartGate::first();
            if ($gate === null) {
                return;
            }
        }

        GateLog::create([
            'tenant_id' => $tenantId ?? $gate->tenant_id,
            'smart_gate_id' => $gate->id,
            'user_id' => $userId,
            'visitor_id' => $visitorId,
            'access_method' => $method,
            'direction' => 'entry',
            'status' => $status,
            'reason' => mb_substr($reason, 0, 255),
            'triggered_at' => now(),
        ]);
    }
}
