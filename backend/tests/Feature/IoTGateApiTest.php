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

    public function test_default_esp32_gate_01_authenticates_and_marks_gate_online_even_in_production(): void
    {
        $prevEnv = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            // Unregistered UID without token must remain rejected in production
            $this->postJson('/api/v1/gate/ping', [
                'device_uid' => 'ESP32-UNKNOWN-PROD-99',
            ])->assertStatus(401);

            // Firmware ESP32-GATE-01 + SECRET-EW-GATE-2026 must connect cleanly in production
            $resp = $this->withHeaders([
                'X-Device-UID' => 'ESP32-GATE-01',
                'X-Device-Token' => 'SECRET-EW-GATE-2026',
            ])->postJson('/api/v1/gate/ping', [
                'device_uid' => 'ESP32-GATE-01',
            ]);

            $resp->assertStatus(200)->assertJson([
                'success' => true,
                'status' => 'online',
                'device_uid' => 'ESP32-GATE-01',
            ]);

            // Any new gate unit with valid shared gate token can also register in production
            $resp2 = $this->withHeaders([
                'X-Device-UID' => 'ESP32-GATE-02',
                'X-Device-Token' => 'SECRET-EW-GATE-2026',
            ])->postJson('/api/v1/gate/ping', [
                'device_uid' => 'ESP32-GATE-02',
            ]);

            $resp2->assertStatus(200)->assertJson([
                'success' => true,
                'status' => 'online',
                'device_uid' => 'ESP32-GATE-02',
            ]);
        } finally {
            $this->app['env'] = $prevEnv;
        }

        $this->assertTrue(\App\Services\RfidService::getGateOnlineStatus()['online']);
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

    public function test_logged_in_resident_can_pair_rfid_via_web(): void
    {
        $warga = User::where('role', 'warga')->first();
        $this->assertNotNull($warga);

        $response = $this->actingAs($warga)->postJson(route('warga.pair-rfid'), [
            'rfid_uid' => '04:A2:3B:8C',
            'scan_method' => 'web_nfc',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'rfid_tag' => 'RFID-04A23B8C',
            'clean_uid' => '04A23B8C',
        ]);

        $this->assertEquals('RFID-04A23B8C', $warga->fresh()->rfid_tag);
    }

    public function test_duplicate_rfid_uid_registration_is_rejected(): void
    {
        $wargaA = User::where('role', 'warga')->first();
        $wargaB = User::where('role', 'warga')->skip(1)->first() ?? User::create([
            'tenant_id' => $wargaA->tenant_id,
            'name' => 'Warga Cadangan',
            'email' => 'cadangan@ewarga.id',
            'password' => bcrypt('password'),
            'role' => 'warga',
            'phone_number' => '089999999999',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
        ]);

        // Pasangkan kartu ke warga A
        $wargaA->update(['rfid_tag' => 'RFID-99A8B7C6']);

        // Warga B mencoba memasangkan nomor kartu yang sama
        $response = $this->actingAs($wargaB)->postJson(route('warga.pair-rfid'), [
            'rfid_uid' => '99:A8:B7:C6',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $this->assertNotEquals('RFID-99A8B7C6', $wargaB->fresh()->rfid_tag);
    }

    public function test_hardware_esp32_live_pairing_session(): void
    {
        $warga = User::where('role', 'warga')->first();

        // 1. Warga memulai sesi pairing di aplikasi web
        $startRes = $this->actingAs($warga)->postJson(route('warga.esp32-pair.start'));
        $startRes->assertStatus(200);
        $startRes->assertJson(['success' => true, 'expires_in' => 60]);

        // 2. Fisik kartu ditempelkan di sensor ESP32 gerbang
        $tapRes = $this->postJson('/api/v1/gate/scan-rfid', [
            'device_uid' => 'ESP32-GATE-TEST-01',
            'rfid_uid' => '7A-8B-9C-0D',
        ]);

        $tapRes->assertStatus(200);
        $tapRes->assertJson([
            'success' => true,
            'status' => 'paired',
            'lcd_line1' => 'KTP TERDAFTAR!',
            'rfid_uid' => 'RFID-7A8B9C0D',
        ]);

        // Kartu langsung terpasang di database
        $this->assertEquals('RFID-7A8B9C0D', $warga->fresh()->rfid_tag);

        // 3. Web app warga memeriksa status pairing
        $statusRes = $this->actingAs($warga)->getJson(route('warga.esp32-pair.status'));
        $statusRes->assertStatus(200);
        $statusRes->assertJson([
            'success' => true,
            'paired' => true,
            'rfid_tag' => 'RFID-7A8B9C0D',
        ]);
    }

    public function test_unapproved_warga_dashboard_shows_all_features_locked(): void
    {
        $newWarga = User::create([
            'tenant_id' => Tenant::first()->id,
            'name' => 'Warga Baru Terdaftar',
            'email' => 'wargabaru@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'warga',
            'phone_number' => '087711223344',
            'verification_status' => 'pending_rw',
            'gate_access_enabled' => false,
            'nik' => '3201234567890099',
        ]);

        $response = $this->actingAs($newWarga)->get(route('warga.index'));

        $response->assertStatus(200);
        // Memastikan banner informasi terkunci tampil
        $response->assertSee('Hak Akses Portal Terkunci: Menunggu Konfirmasi Admin');
        $response->assertSee('Sensor e-KTP Fisik');
        // Memastikan seluruh fitur tetap tampil dalam mode terkunci
        $response->assertSee('STATUS PALANG GERBANG: TERKUNCI');
        $response->assertSee('Buat Izin Masuk Tamu / Kurir');
        $response->assertSee('Iuran Lingkungan Bulan Ini');
        $response->assertSee('Layanan Surat Pengantar RT/RW');
    }

    public function test_simulator_rejects_unregistered_rfid_card(): void
    {
        $response = $this->postJson(route('simulator.trigger'), [
            'method' => 'rfid_unregistered',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonFragment([
            'status' => 'denied',
        ]);
    }
}
