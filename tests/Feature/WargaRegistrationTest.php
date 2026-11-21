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
        $response->assertSee('Verifikasi e-KTP');
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
}
