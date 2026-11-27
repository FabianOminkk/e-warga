<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('smart_gates') || !Schema::hasTable('tenants')) {
            return;
        }

        $tenantId = DB::table('tenants')->orderBy('id')->value('id');
        if ($tenantId === null) {
            return;
        }

        $now = now();
        $defaultDevices = [
            [
                'device_uid' => 'ESP32-GATE-01',
                'name' => 'Gerbang Utama Smart Gate (ESP32-GATE-01)',
                'secret_token' => 'SECRET-EW-GATE-2026',
            ],
            [
                'device_uid' => 'esp32-scanner-01',
                'name' => 'Scanner RFID ESP32 (esp32-scanner-01)',
                'secret_token' => 'smartrw-rfid-device',
            ],
        ];

        foreach ($defaultDevices as $device) {
            $exists = DB::table('smart_gates')
                ->where('device_uid', $device['device_uid'])
                ->exists();

            if (!$exists) {
                DB::table('smart_gates')->insert([
                    'tenant_id' => $tenantId,
                    'name' => $device['name'],
                    'device_uid' => $device['device_uid'],
                    'secret_token' => $device['secret_token'],
                    'ip_address' => null,
                    'status' => 'offline',
                    'last_ping_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Biarkan data perangkat tetap aman saat rollback parsial
    }
};
