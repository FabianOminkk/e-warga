<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WargaRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected House $house;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Cluster Melati Mas',
            'slug' => 'cluster-melati-mas',
            'rt_rw' => 'RT 05 / RW 12',
            'city' => 'Jakarta Selatan',
            'is_active' => true,
        ]);

        $this->house = House::create([
            'tenant_id' => $this->tenant->id,
            'block_name' => 'Blok B',
            'house_number' => '10',
            'full_address' => 'Blok B No. 10',
            'occupancy_status' => 'occupied',
        ]);
    }

    public function test_registration_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Formulir Pendaftaran Warga');
        $response->assertSee('Daftar via Gmail');
        $response->assertSee('Registrasi Kilat');
        $response->assertSee($this->tenant->name);
    }

    public function test_api_get_houses_by_tenant_returns_json(): void
    {
        $response = $this->getJson("/api/tenants/{$this->tenant->id}/houses");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonFragment([
                'block_name' => 'Blok B',
                'house_number' => '10',
            ]);
    }

    public function test_citizen_can_register_using_gmail_fast_mode(): void
    {
        $payload = [
            'registration_method' => 'gmail',
            'tenant_id' => $this->tenant->id,
            'name' => 'Ahmad Warga Gmail',
            'email' => 'ahmad.warga@gmail.com',
            'phone_number' => '081298765432',
            'house_id' => $this->house->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_family_head' => '1',
        ];

        $response = $this->post('/register', $payload);

        $response->assertRedirect(route('warga.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'ahmad.warga@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('warga', $user->role);
        $this->assertEquals('unverified', $user->verification_status);
        $this->assertFalse($user->gate_access_enabled);
        $this->assertStringStartsWith('RFID-TMP-', $user->rfid_tag);
    }

    public function test_citizen_can_register_using_ktp_verification_mode(): void
    {
        Storage::fake('local');

        $ktpFile = UploadedFile::fake()->image('ktp_warga.jpg', 600, 400);

        $payload = [
            'registration_method' => 'ktp',
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi e-KTP Mandiri',
            'email' => 'budi.ktp@gmail.com',
            'phone_number' => '081345678901',
            'nik' => '3201234567890001',
            'gender' => 'male',
            'address_ktp' => 'Jl. Mawar Indah No. 45 RT 02 RW 05',
            'ktp_photo' => $ktpFile,
            'new_block_name' => 'Blok C',
            'new_house_number' => '22',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_family_head' => '1',
        ];

        $response = $this->post('/register', $payload);

        $response->assertRedirect(route('warga.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'budi.ktp@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('warga', $user->role);
        $this->assertEquals('3201234567890001', $user->nik);
        $this->assertEquals('pending_rw', $user->verification_status);
        $this->assertFalse($user->gate_access_enabled);
        $this->assertEquals('RFID-3201234567890001', $user->rfid_tag);
        $this->assertNotNull($user->ktp_photo);

        // Pastikan rumah baru otomatis terdaftar di lingkungan ini
        $newHouse = House::where('tenant_id', $this->tenant->id)
            ->where('block_name', 'BLOK C')
            ->where('house_number', '22')
            ->first();
        $this->assertNotNull($newHouse);
        $this->assertEquals($newHouse->id, $user->house_id);
    }

    public function test_registration_validation_fails_for_duplicate_or_invalid_data(): void
    {
        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Existing User',
            'email' => 'existing@gmail.com',
            'phone_number' => '081111111111',
            'nik' => '3201111111111111',
            'password' => bcrypt('password123'),
            'role' => 'warga',
        ]);

        // Uji email duplikat
        $response = $this->post('/register', [
            'registration_method' => 'gmail',
            'tenant_id' => $this->tenant->id,
            'name' => 'New User',
            'email' => 'existing@gmail.com',
            'phone_number' => '082222222222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);

        // Uji NIK harus tepat 16 digit pada mode KTP
        $responseKtp = $this->post('/register', [
            'registration_method' => 'ktp',
            'tenant_id' => $this->tenant->id,
            'name' => 'New User KTP',
            'email' => 'unique@gmail.com',
            'phone_number' => '083333333333',
            'nik' => '12345', // kurang dari 16 digit
            'gender' => 'male',
            'address_ktp' => 'Jl. Anggrek',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseKtp->assertSessionHasErrors(['nik']);
    }

    public function test_unapproved_warga_dashboard_shows_locked_state_screen_only(): void
    {
        $unapprovedWarga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Baru Terkunci',
            'email' => 'terkunci@warga.id',
            'phone_number' => '081299998888',
            'password' => bcrypt('password123'),
            'role' => 'warga',
            'verification_status' => 'pending_rw',
            'gate_access_enabled' => false,
            'status' => 'active',
        ]);

        $response = $this->actingAs($unapprovedWarga)->get(route('warga.index'));

        $response->assertStatus(200);
        $response->assertSee('Hak Akses Portal Terkunci: Menunggu Konfirmasi Admin');
        $response->assertSee('Menunggu Konfirmasi Admin / Pengurus RT/RW');
        // Warga yang belum di-ACC TIDAK BOLEH melihat tombol operasional buka gerbang
        $response->assertDontSee('BUKA GERBANG PORTAL');
    }

    public function test_citizen_can_register_with_rfid_card_tap_pairing(): void
    {
        Storage::fake('local');

        $payload = [
            'registration_method' => 'ktp',
            'tenant_id' => $this->tenant->id,
            'name' => 'Warga RFID Tap',
            'email' => 'warga.rfid@gmail.com',
            'phone_number' => '081234567899',
            'nik' => '3201998877660001',
            'gender' => 'female',
            'birth_place' => 'Bandung',
            'birth_date' => '1995-08-17',
            'address_ktp' => 'Jl. Boulevard Blok D No. 5',
            'rfid_tag' => 'E2:00:00:1B:4D',
            'new_block_name' => 'Blok D',
            'new_house_number' => '05',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_family_head' => '0',
        ];

        $response = $this->post('/register', $payload);

        $response->assertRedirect(route('warga.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'warga.rfid@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('RFID-E200001B4D', $user->rfid_tag);
        $this->assertEquals('pending_rw', $user->verification_status);
        $this->assertFalse($user->gate_access_enabled);
        $this->assertEquals('Bandung', $user->birth_place);
    }

    public function test_registration_rejects_duplicate_rfid_card(): void
    {
        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Owner of Card',
            'email' => 'owner.card@gmail.com',
            'phone_number' => '081999999999',
            'nik' => '3201888877770002',
            'rfid_tag' => 'RFID-E200001B4D',
            'password' => bcrypt('password123'),
            'role' => 'warga',
        ]);

        $payload = [
            'registration_method' => 'ktp',
            'tenant_id' => $this->tenant->id,
            'name' => 'Duplicate Card Claimer',
            'email' => 'claimer@gmail.com',
            'phone_number' => '081777777777',
            'nik' => '3201555544440003',
            'gender' => 'male',
            'address_ktp' => 'Jl. Anggrek No. 1',
            'rfid_tag' => 'E2 00 00 1B 4D',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $payload);

        $response->assertSessionHasErrors(['rfid_tag']);
    }

    public function test_esp32_pairing_api_session_lifecycle(): void
    {
        // 1. Mulai sesi pairing di formulir pendaftaran
        $startResponse = $this->postJson(route('api.register.esp32-pair.start'), [
            'tenant_id' => $this->tenant->id,
        ]);
        $startResponse->assertStatus(200)
            ->assertJson([
                'status' => 'waiting',
                'active' => true,
            ]);

        // 2. Cek status pairing saat belum ada kartu ditempelkan
        $statusResponse = $this->getJson(route('api.register.esp32-pair.status', [
            'tenant_id' => $this->tenant->id,
        ]));
        $statusResponse->assertStatus(200)
            ->assertJson([
                'status' => 'waiting',
            ]);

        // 3. Simulasikan gate ESP32 membaca kartu warga
        $gate = \App\Models\SmartGate::create([
            'tenant_id' => $this->tenant->id,
            'device_uid' => 'ESP32-GATE-REG-01',
            'name' => 'Gate Utama RT 05',
            'status' => 'online',
        ]);

        $scanResponse = $this->postJson('/api/v1/gate/scan-rfid', [
            'device_uid' => $gate->device_uid,
            'rfid_uid' => '5A:4B:3C:2D',
        ]);

        $scanResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'paired',
                'rfid_uid' => 'RFID-5A4B3C2D',
            ]);

        // 4. Cek status formulir pendaftaran kembali, UID kartu harus sudah tertangkap
        $finalStatusResponse = $this->getJson(route('api.register.esp32-pair.status', [
            'tenant_id' => $this->tenant->id,
        ]));
        $finalStatusResponse->assertStatus(200)
            ->assertJson([
                'status' => 'paired',
                'rfid_tag' => 'RFID-5A4B3C2D',
            ]);
    }

    public function test_esp32_standalone_registration_desk_device_flow(): void
    {
        // 1. Tes ping perangkat scanner meja
        $pingResponse = $this->getJson('/api/v1/registration-device/ping');
        $pingResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'online',
                ],
            ]);

        // 2. Scanner ESP32 mendeteksi e-KTP baru
        $scanResponse = $this->postJson('/api/v1/registration-device/scan', [
            'device_uid' => 'ESP32-REGIS-DESK-01',
            'rfid_uid' => '7E:8D:9C:0A',
            'tenant_id' => $this->tenant->id,
        ]);

        $scanResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'paired',
                'lcd_line1' => 'KTP TERCATAT!',
                'lcd_line2' => 'REGIS KILAT OK',
                'rfid_uid' => 'RFID-7E8D9C0A',
            ]);

        // 3. Formulir pendaftaran kilat menerima UID secara real-time
        $statusResponse = $this->getJson(route('api.register.esp32-pair.status', [
            'tenant_id' => $this->tenant->id,
        ]));
        $statusResponse->assertStatus(200)
            ->assertJson([
                'status' => 'paired',
                'rfid_tag' => 'RFID-7E8D9C0A',
            ]);

        // 4. Jika kartu yang sama di-tap lagi oleh orang lain, tolak dengan status 422
        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Warga Pemilik Kartu',
            'email' => 'kartu.terdaftar@gmail.com',
            'phone_number' => '081233334444',
            'nik' => '3201444455550001',
            'rfid_tag' => 'RFID-7E8D9C0A',
            'password' => bcrypt('password123'),
            'role' => 'warga',
        ]);

        $duplicateScanResponse = $this->postJson('/api/v1/registration-device/scan', [
            'device_uid' => 'ESP32-REGIS-DESK-01',
            'rfid_uid' => '7E:8D:9C:0A',
            'tenant_id' => $this->tenant->id,
        ]);

        $duplicateScanResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status' => 'denied',
                'lcd_line1' => 'REGIS GAGAL!',
                'lcd_line2' => 'KARTU SUDAH ADA',
            ]);
    }
}
