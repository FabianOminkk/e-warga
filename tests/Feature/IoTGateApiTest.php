<?php

namespace Tests\Feature;

use App\Models\GateLog;
use App\Models\House;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IoTGateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_esp32_can_ping_and_receives_idle_state(): void
    {
        $response = $this->postJson('/api/v1/gate/ping', [
            'device_uid' => 'ESP32-GATE-TEST-01',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'online',
            'action' => 'idle',
        ]);

        $this->assertDatabaseHas('smart_gates', [
            'device_uid' => 'ESP32-GATE-TEST-01',
            'status' => 'online',
        ]);
    }

    public function test_esp32_receives_remote_open_command_when_mobile_button_clicked(): void
    {
        Cache::put('gate_remote_open_global', [
            'user_name' => 'Bpk. Bambang Pamungkas',
            'reason' => 'Buka via Tombol HP',
            'time' => now()->toIso8601String(),
        ], 15);

        $response = $this->postJson('/api/v1/gate/ping', [
            'device_uid' => 'ESP32-GATE-TEST-01',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'open',
            'open_duration' => 5,
            'triggered_by' => 'Bpk. Bambang Pamungkas',
        ]);
    }

    public function test_verified_resident_can_open_gate_via_rfid_scan(): void
    {
        $verifiedWarga = User::where('role', 'warga')
            ->where('verification_status', 'approved')
            ->where('gate_access_enabled', true)
            ->first();

        $this->assertNotNull($verifiedWarga);

        $response = $this->postJson('/api/v1/gate/scan-rfid', [
            'device_uid' => 'ESP32-GATE-TEST-01',
            'rfid_uid' => $verifiedWarga->rfid_tag,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'granted',
            'action' => 'open',
            'open_duration' => 5,
            'resident_name' => $verifiedWarga->name,
        ]);

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $verifiedWarga->id,
            'status' => 'granted',
            'access_method' => 'rfid_card',
        ]);
    }

    public function test_unverified_resident_is_denied_access_via_rfid(): void
    {
        $unverifiedWarga = User::where('role', 'warga')
            ->where('verification_status', '!=', 'approved')
            ->first();

        $this->assertNotNull($unverifiedWarga);

        $response = $this->postJson('/api/v1/gate/scan-rfid', [
            'device_uid' => 'ESP32-GATE-TEST-01',
            'rfid_uid' => $unverifiedWarga->rfid_tag ?? $unverifiedWarga->nik,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'status' => 'denied',
        ]);

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $unverifiedWarga->id,
            'status' => 'denied',
        ]);
    }

    public function test_unknown_rfid_card_is_denied(): void
    {
        $response = $this->postJson('/api/v1/gate/scan-rfid', [
            'device_uid' => 'ESP32-GATE-TEST-01',
            'rfid_uid' => 'UNKNOWN-CARD-999999',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'status' => 'denied',
            'lcd_line1' => 'AKSES DITOLAK!',
            'lcd_line2' => 'KARTU TAK DIKENAL',
        ]);
    }

    public function test_valid_visitor_qr_is_accepted_and_marked_used(): void
    {
        $tenant = Tenant::first();
        $hostHouse = House::first();
        $wargaUser = User::where('role', 'warga')->first();

        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'host_house_id' => $hostHouse->id,
            'invited_by_user_id' => $wargaUser->id,
            'guest_name' => 'Kurir Paket J&T',
            'guest_type' => 'courier',
            'vehicle_plate' => 'B 1234 ABC',
            'qr_token' => 'PASS-TEST-123',
            'valid_from' => now()->subHour(),
            'valid_until' => now()->addHours(2),
            'is_used' => false,
        ]);

        $response = $this->postJson('/api/v1/gate/scan-qr', [
            'device_uid' => 'ESP32-GATE-TEST-01',
            'qr_token' => 'PASS-TEST-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'granted',
            'action' => 'open',
            'guest_name' => 'Kurir Paket J&T',
        ]);

        $this->assertTrue($visitor->fresh()->is_used);
    }
}
