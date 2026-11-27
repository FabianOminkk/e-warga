<?php

namespace Tests\Feature;

use App\Models\GateLog;
use App\Models\House;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RfidSharedDeviceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private House $house;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Cluster Harmoni',
            'slug' => 'cluster-harmoni',
            'rt_rw' => 'RT 03 / RW 07',
            'is_active' => true,
        ]);

        $this->house = House::create([
            'tenant_id' => $this->tenant->id,
            'block_name' => 'Blok C',
            'house_number' => '05',
            'full_address' => 'Blok C No. 05',
            'occupancy_status' => 'occupied',
        ]);
    }

    public function test_shared_esp32_heartbeat_marks_reader_and_dashboard_online(): void
    {
        $response = $this->withHeaders([
            'X-Device-Key' => 'smartrw-rfid-device',
        ])->postJson('/api/rfid/heartbeat', [
            'device_id' => 'esp32-scanner-01',
            'ip' => '192.168.43.50',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'online',
                'device_id' => 'esp32-scanner-01',
                'ip' => '192.168.43.50',
            ]);

        $this->assertTrue((bool) Cache::get('rfid_reader_online'));

        $this->getJson('/api/rfid/status')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'online' => true,
                'status' => 'online',
            ]);

        $warga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Test Online',
            'email' => 'online.test@warga.id',
            'phone_number' => '081211112222',
            'password' => bcrypt('password123'),
            'role' => 'warga',
        ]);

        $this->actingAs($warga)
            ->getJson(route('warga.esp32-status'))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'online' => true,
                'status' => 'online',
                'device_id' => 'esp32-scanner-01',
                'ip_address' => '192.168.43.50',
            ]);
    }

    public function test_invalid_device_key_or_bearer_token_is_rejected_with_401(): void
    {
        $this->withHeaders([
            'X-Device-Key' => 'invalid-key',
        ])->postJson('/api/rfid/tap', [
            'uid' => 'E3A82C10',
            'device_id' => 'esp32-scanner-01',
        ])->assertStatus(401);

        $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'X-Device-Key' => 'smartrw-rfid-device',
        ])->postJson('/api/rfid/tap', [
            'uid' => 'E3A82C10',
            'device_id' => 'esp32-scanner-01',
        ])->assertStatus(401);
    }

    public function test_tap_validates_required_string_uid(): void
    {
        $this->withHeaders([
            'Authorization' => 'Bearer smartrw-rfid-device',
            'X-Device-Key' => 'smartrw-rfid-device',
        ])->postJson('/api/rfid/tap', [
            'device_id' => 'esp32-scanner-01',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['uid']);
    }

    public function test_unregistered_uid_is_uppercased_and_returns_http_200(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer smartrw-rfid-device',
            'X-Device-Key' => 'smartrw-rfid-device',
        ])->postJson('/api/rfid/tap', [
            'uid' => 'e3a82c10',
            'device_id' => 'esp32-scanner-01',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'uid' => 'E3A82C10',
                'formatted_uid' => 'RFID-E3A82C10',
                'mode' => 'unregistered',
                'gate_status' => 'denied',
            ]);

        $this->assertDatabaseHas('gate_logs', [
            'status' => 'denied',
            'access_method' => 'rfid_card',
        ]);
    }

    public function test_tap_binds_card_to_resident_in_active_pairing_session(): void
    {
        $secondTenant = Tenant::create([
            'name' => 'Cluster Kedua',
            'slug' => 'cluster-kedua',
            'rt_rw' => 'RT 09 / RW 02',
            'is_active' => true,
        ]);

        $warga = User::create([
            'tenant_id' => $secondTenant->id,
            'house_id' => $this->house->id,
            'name' => 'Fabian Warga',
            'email' => 'fabian.pair@warga.id',
            'phone_number' => '081234567899',
            'password' => bcrypt('password123'),
            'role' => 'warga',
            'rfid_tag' => null,
        ]);

        $this->actingAs($warga)->postJson(route('warga.esp32-pair.start'))->assertStatus(200);

        $tapRes = $this->withHeaders([
            'X-Device-Key' => 'smartrw-rfid-device',
        ])->postJson('/api/rfid/tap', [
            'uid' => 'E3A82C10',
            'device_id' => 'esp32-scanner-01',
        ]);

        $tapRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'paired' => true,
                'mode' => 'pairing_warga',
                'uid' => 'E3A82C10',
                'formatted_uid' => 'RFID-E3A82C10',
                'resident_name' => 'Fabian Warga',
            ]);

        $this->assertEquals('RFID-E3A82C10', $warga->fresh()->rfid_tag);

        $this->actingAs($warga)
            ->getJson(route('warga.esp32-pair.status'))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'paired' => true,
                'rfid_tag' => 'RFID-E3A82C10',
            ]);
    }

    public function test_tap_grants_portal_access_for_verified_resident_and_denies_pending(): void
    {
        $verified = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Terverifikasi',
            'email' => 'verified@warga.id',
            'phone_number' => '081200001111',
            'password' => bcrypt('password123'),
            'role' => 'warga',
            'rfid_tag' => 'RFID-AA11BB22',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
        ]);

        $pending = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Belum ACC',
            'email' => 'pending@warga.id',
            'phone_number' => '081200002222',
            'password' => bcrypt('password123'),
            'role' => 'warga',
            'rfid_tag' => 'RFID-CC33DD44',
            'verification_status' => 'pending_rw',
            'gate_access_enabled' => false,
        ]);

        $this->withHeaders([
            'X-Device-Key' => 'smartrw-rfid-device',
        ])->postJson('/api/rfid/tap', [
            'uid' => 'AA11BB22',
            'device_id' => 'esp32-scanner-01',
        ])->assertStatus(200)
            ->assertJson([
                'success' => true,
                'uid' => 'AA11BB22',
                'mode' => 'gate_access',
                'gate_status' => 'granted',
                'action' => 'open',
                'resident_name' => 'Warga Terverifikasi',
            ]);

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $verified->id,
            'status' => 'granted',
            'access_method' => 'rfid_card',
        ]);

        $this->withHeaders([
            'X-Device-Key' => 'smartrw-rfid-device',
        ])->postJson('/api/rfid/tap', [
            'uid' => 'CC33DD44',
            'device_id' => 'esp32-scanner-01',
        ])->assertStatus(200)
            ->assertJson([
                'success' => true,
                'uid' => 'CC33DD44',
                'mode' => 'gate_access',
                'gate_status' => 'denied',
                'action' => 'denied',
                'resident_name' => 'Warga Belum ACC',
            ]);

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $pending->id,
            'status' => 'denied',
            'access_method' => 'rfid_card',
        ]);
    }

    public function test_smartgate_defaults_offline_until_esp32_connects_and_reverts_on_disconnect(): void
    {
        $gate = \App\Models\SmartGate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gerbang Utama Cluster',
            'device_uid' => 'ESP32-GATE-LIVE-01',
        ]);

        $warga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga SmartGate',
            'email' => 'sg.warga@warga.id',
            'phone_number' => '081200003333',
            'password' => bcrypt('password123'),
            'role' => 'warga',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
        ]);

        $satpam = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Satpam SmartGate',
            'email' => 'sg.satpam@warga.id',
            'phone_number' => '081200004444',
            'password' => bcrypt('password123'),
            'role' => 'satpam',
        ]);

        // 1. Sebelum ESP32 konek -> semua status harus OFFLINE & remote open ditolak
        $this->assertFalse($gate->fresh()->isOnline());
        $this->assertSame('offline', $gate->fresh()->status);

        $this->getJson('/api/rfid/status')
            ->assertStatus(200)
            ->assertJson(['online' => false, 'status' => 'offline']);

        $this->get('/')->assertStatus(200)->assertSee('OFFLINE • BELUM TERHUBUNG');
        $this->actingAs($warga)->get('/warga/smart-gate')->assertStatus(200)->assertSee('ESP32 Gate: OFFLINE');
        $this->actingAs($satpam)->get('/satpam')->assertStatus(200)->assertSee('OFFLINE • ESP32 BELUM TERHUBUNG');

        $this->actingAs($warga)->postJson(route('warga.open-gate'))
            ->assertStatus(422)
            ->assertJson(['success' => false, 'offline' => true]);

        $this->actingAs($satpam)->postJson(route('satpam.trigger-manual'))
            ->assertStatus(422)
            ->assertJson(['success' => false, 'offline' => true]);

        // 2. Desk scanner SAJA konek -> SmartGate portal tetap OFFLINE
        $this->getJson("/api/v1/registration-device/ping?device_uid=ESP32-REGIS-DESK-01&tenant_id={$this->tenant->id}")
            ->assertStatus(200);
        $this->getJson('/api/rfid/status')
            ->assertStatus(200)
            ->assertJson(['online' => false, 'device_online' => true]);

        // 3. ESP32 SmartGate terhubung -> SmartGate langsung ONLINE dan remote open diizinkan
        $this->withHeaders(['X-Device-Key' => 'smartrw-rfid-device'])
            ->postJson('/api/rfid/heartbeat', [
                'device_id' => 'ESP32-GATE-LIVE-01',
                'ip' => '192.168.43.55',
                'tenant_id' => $this->tenant->id,
            ])->assertStatus(200);

        $this->assertTrue($gate->fresh()->isOnline());
        $this->getJson('/api/rfid/status')
            ->assertStatus(200)
            ->assertJson(['online' => true, 'status' => 'online']);

        $this->get('/')->assertStatus(200)->assertSee('ONLINE • TERHUBUNG');
        $this->actingAs($warga)->postJson(route('warga.open-gate'))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // 4. Setelah heartbeat ESP32 putus > 30 detik -> otomatis kembali OFFLINE
        $this->travel(35)->seconds();
        $this->getJson('/api/rfid/status')
            ->assertStatus(200)
            ->assertJson(['online' => false, 'status' => 'offline']);
        $this->assertSame('offline', $gate->fresh()->status);
        $this->actingAs($warga)->postJson(route('warga.open-gate'))
            ->assertStatus(422)
            ->assertJson(['success' => false, 'offline' => true]);
    }
}
