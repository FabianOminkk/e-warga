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

        $response->assertRedirect(route('warga.profile'));
        $response->assertSessionHas('success');

        $warga->refresh();
        $this->assertEquals('pending_rw', $warga->verification_status);
        $this->assertEquals('3275011902890005', $warga->nik);
        $this->assertEquals('RFID-3275011902890005', $warga->rfid_tag);
        $this->assertFalse($warga->gate_access_enabled);
    }

    public function test_warga_can_access_dedicated_profile_page(): void
    {
        $warga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Test',
            'email' => 'warga.test@ewarga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'status' => 'active',
        ]);

        $this->actingAs($warga);

        $response = $this->get(route('warga.profile'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun & Kelengkapan Identitas KTP', false);
        $response->assertSee('Nomor Induk Kependudukan (NIK - Wajib 16 Digit)');
    }

    public function test_warga_views_schedules_as_siap_satpam_and_cannot_see_ganti_mode(): void
    {
        $warga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Test Siskamling',
            'email' => 'siskamling@ewarga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'status' => 'active',
        ]);

        $this->actingAs($warga);

        $response = $this->get(route('schedules.index'));
        $response->assertStatus(200);
        $response->assertSee('Siap Satpam & Jadwal Keamanan Bulan Ini', false);
        // Warga MUST NOT see Ganti Mode button
        $response->assertDontSee('Ganti Mode: Beralih ke Perkampungan');
        $response->assertDontSee('Ganti Mode: Beralih ke Cluster');
        // Warga MUST NOT see Tambah Jadwal Dinas button
        $response->assertDontSee('➕ Tambah Jadwal Dinas');
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

    public function test_ktp_photo_privacy_controls_protect_citizen_identity(): void
    {
        $wargaOwner = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Pemilik KTP',
            'email' => 'owner@warga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'ktp_photo' => 'uploads/ktp/sample_ktp_siti.svg',
            'verification_status' => 'pending_rw',
            'status' => 'active',
        ]);

        $otherWarga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Asing',
            'email' => 'other@warga.id',
            'password' => bcrypt('warga123'),
            'role' => 'warga',
            'status' => 'active',
        ]);

        // 1. Tamu tanpa login ditolak dan diarahkan ke login
        $this->get(route('warga.ktp.show', $wargaOwner->id))->assertRedirect(route('login'));

        // 2. Warga lain tidak boleh mengintip KTP orang lain (403 Forbidden - UU PDP)
        $this->actingAs($otherWarga)->get(route('warga.ktp.show', $wargaOwner->id))->assertStatus(403);

        // 3. Warga pemilik KTP dapat melihat berkasnya sendiri (200 OK)
        $this->actingAs($wargaOwner)->get(route('warga.ktp.show', $wargaOwner->id))->assertStatus(200);

        // 4. Pengurus RW yang bertugas berhak memeriksa KTP untuk verifikasi (200 OK)
        $this->actingAs($this->ketuaRw)->get(route('warga.ktp.show', $wargaOwner->id))->assertStatus(200);
    }
}

