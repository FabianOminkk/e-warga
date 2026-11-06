<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WargaKtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $ketuaRw;
    protected SmartGate $gate;
    protected House $house;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Cluster Melati Mas',
            'slug' => 'cluster-melati-mas',
            'rt_rw' => 'RT 05 / RW 12',
            'is_active' => true,
        ]);

        $this->house = House::create([
            'tenant_id' => $this->tenant->id,
            'block_name' => 'Blok C',
            'house_number' => '12',
            'full_address' => 'Jl. Melati Mas No. 12',
            'occupancy_status' => 'occupied',
        ]);

        $this->gate = SmartGate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gerbang Utama',
            'device_uid' => 'ESP32-TEST-GATE',
            'status' => 'online',
        ]);

        $this->ketuaRw = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bpk. H. Rahmat Hidayat (Ketua RW 02)',
            'email' => 'ketuarw@ewarga.id',
            'password' => bcrypt('admin123'),
            'role' => 'ketua_rw',
            'status' => 'active',
        ]);
    }

    public function test_newly_registered_warga_is_locked_and_cannot_open_gate(): void
    {
        $newWarga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Hendra Gunawan',
            'email' => 'hendra@warga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'verification_status' => 'unverified',
            'gate_access_enabled' => false,
            'status' => 'active',
        ]);

        $this->actingAs($newWarga);

        $response = $this->postJson(route('warga.open-gate'));

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'locked' => true,
        ]);

        $this->assertDatabaseMissing('gate_logs', [
            'user_id' => $newWarga->id,
            'status' => 'granted',
        ]);
    }

    public function test_warga_submitting_ktp_profile_moves_to_pending_rw_review(): void
    {
        $warga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Hendra Gunawan',
            'email' => 'hendra@warga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'verification_status' => 'unverified',
            'gate_access_enabled' => false,
            'status' => 'active',
        ]);

        $this->actingAs($warga);

        $response = $this->post(route('warga.profile.update'), [
            'name' => 'Hendra Gunawan S.Kom',
            'phone_number' => '081234567899',
            'nik' => '3275011902890005',
            'birth_date' => '1989-02-19',
            'birth_place' => 'Bandung',
            'gender' => 'male',
            'religion' => 'Islam',
            'occupation' => 'Software Engineer',
            'address_ktp' => 'Jl. Melati Mas IX No. 12, Kel. Coblong',
            'house_id' => $this->house->id,
        ]);

        $response->assertRedirect(route('warga.index'));
        $response->assertSessionHas('success');

        $warga->refresh();
        $this->assertEquals('pending_rw', $warga->verification_status);
        $this->assertEquals('3275011902890005', $warga->nik);
        $this->assertEquals('RFID-3275011902890005', $warga->rfid_tag);
        $this->assertFalse($warga->gate_access_enabled);
    }

    public function test_ketua_rw_can_approve_warga_and_gate_access_is_activated(): void
    {
        $warga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Ibu Siti Rahmawati',
            'email' => 'siti@warga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'nik' => '3275014408900003',
            'rfid_tag' => 'RFID-3275014408900003',
            'verification_status' => 'pending_rw',
            'gate_access_enabled' => false,
            'status' => 'active',
        ]);

        $this->actingAs($this->ketuaRw);

        $response = $this->post(route('rw.verifications.approve', $warga->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $warga->refresh();
        $this->assertEquals('approved', $warga->verification_status);
        $this->assertTrue($warga->gate_access_enabled);
        $this->assertEquals($this->ketuaRw->id, $warga->verified_by_user_id);
        $this->assertNotNull($warga->verified_at);

        // Now test that warga can open gate
        $this->actingAs($warga);
        $gateResponse = $this->postJson(route('warga.open-gate'));
        $gateResponse->assertStatus(200);
        $gateResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $warga->id,
            'status' => 'granted',
        ]);
    }

    public function test_simulator_denies_unverified_warga_and_permits_approved_warga(): void
    {
        $unverifiedWarga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Belum Verifikasi',
            'email' => 'unverified@warga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'verification_status' => 'unverified',
            'gate_access_enabled' => false,
            'status' => 'active',
        ]);

        $approvedWarga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga ACC RW',
            'email' => 'approved@warga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'nik' => '3275011205820001',
            'rfid_tag' => 'RFID-3275011205820001',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
            'status' => 'active',
        ]);

        // 1. Test unverified citizen in simulator: Must be rejected (403)
        $simDenied = $this->postJson(route('simulator.trigger'), [
            'method' => 'rfid_card',
            'user_id' => $unverifiedWarga->id,
        ]);
        $simDenied->assertStatus(403);
        $simDenied->assertJson(['success' => false]);

        // 2. Test approved citizen in simulator: Must be granted (200)
        $simGranted = $this->postJson(route('simulator.trigger'), [
            'method' => 'rfid_card',
            'user_id' => $approvedWarga->id,
        ]);
        $simGranted->assertStatus(200);
        $simGranted->assertJson(['success' => true]);
    }
}
