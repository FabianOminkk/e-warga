<?php

namespace Tests\Feature;

use App\Models\LetterRequest;
use App\Models\PosyanduMember;
use App\Models\PosyanduRecord;
use App\Models\SecuritySchedule;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EwargaNewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_access_jadwal_keamanan_page(): void
    {
        $response = $this->get('/jadwal-keamanan');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Keamanan');
        $response->assertSee('Pak Slamet');
    }

    public function test_theme_switcher_rendered_in_layout(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('localStorage.getItem(\'theme\')', false);
        $response->assertSee('Toggle Dark / Light Theme');
    }

    public function test_can_toggle_environment_mode(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $this->actingAs($superAdmin);

        $tenant = Tenant::first();
        $initialMode = $tenant->environment_type;

        $response = $this->post('/toggle-mode');
        $response->assertRedirect();

        $tenant->refresh();
        $this->assertNotEquals($initialMode, $tenant->environment_type);
    }

    public function test_regular_warga_cannot_toggle_environment_mode(): void
    {
        $warga = User::where('role', 'warga')->first();
        $this->actingAs($warga);

        $tenant = Tenant::first();
        $initialMode = $tenant->environment_type;

        $response = $this->post('/toggle-mode');
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $tenant->refresh();
        $this->assertEquals($initialMode, $tenant->environment_type);
    }

    public function test_can_checkin_security_schedule(): void
    {
        $schedule = SecuritySchedule::where('status', 'scheduled')->first();
        $this->assertNotNull($schedule);

        $response = $this->post("/jadwal-keamanan/{$schedule->id}/checkin");
        $response->assertRedirect();

        $schedule->refresh();
        $this->assertEquals('present', $schedule->status);
        $this->assertNotNull($schedule->checked_in_at);
    }

    public function test_can_access_posyandu_page(): void
    {
        $response = $this->get('/posyandu');
        $response->assertStatus(200);
        $response->assertSee('Modul Posyandu Digital RT/RW');
        $response->assertSee('Balita');
        $response->assertSee('Rayyan Al-Fatih');
    }

    public function test_can_register_new_posyandu_member(): void
    {
        $response = $this->post('/posyandu/members', [
            'name' => 'Fathan Al-Ghifari',
            'category' => 'balita',
            'gender' => 'L',
            'birth_date' => '2025-06-15',
            'family_card_number' => '3216012903820099',
            'nik' => '3216011506250001',
            'parent_name' => 'Bpk. Ahmad & Ibu Siti',
            'address' => 'Blok E No. 01',
            'phone' => '081234567899',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posyandu_members', [
            'name' => 'Fathan Al-Ghifari',
            'category' => 'balita',
        ]);
    }

    public function test_can_record_posyandu_checkup(): void
    {
        $member = PosyanduMember::where('category', 'balita')->first();
        $this->assertNotNull($member);

        $response = $this->post('/posyandu/records', [
            'posyandu_member_id' => $member->id,
            'checkup_date' => date('Y-m-d'),
            'weight_kg' => 12.5,
            'height_cm' => 88.0,
            'vitamins_or_vaccines' => 'Vitamin A Kapsul Biru',
            'examiner_name' => 'Bidan Nina',
            'action_plan' => 'Gizi baik, perkembangan optimal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posyandu_records', [
            'posyandu_member_id' => $member->id,
            'weight_kg' => 12.5,
        ]);
    }

    public function test_can_access_letters_page(): void
    {
        $response = $this->get('/surat-pengantar');
        $response->assertStatus(200);
        $response->assertSee('Layanan Surat Pengantar RT Online');
    }

    public function test_can_request_rt_letter(): void
    {
        $response = $this->post('/surat-pengantar', [
            'letter_type' => 'pengantar_kk',
            'applicant_name' => 'Bambang Pamungkas',
            'nik' => '3216012903820001',
            'house_block' => 'Blok C No. 12',
            'purpose' => 'Penambahan anggota keluarga baru pada kartu keluarga',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('letter_requests', [
            'letter_type' => 'pengantar_kk',
            'applicant_name' => 'Bambang Pamungkas',
            'status' => 'pending',
        ]);
    }

    public function test_can_approve_letter_and_verify_qr_code(): void
    {
        $letter = LetterRequest::where('status', 'pending')->first();
        $this->assertNotNull($letter);

        $response = $this->post("/surat-pengantar/{$letter->id}/approve");
        $response->assertRedirect();

        $letter->refresh();
        $this->assertEquals('approved', $letter->status);
        $this->assertNotNull($letter->letter_number);
        $this->assertNotNull($letter->qr_verification_code);

        // Test public verification endpoint
        $verifyResponse = $this->get("/surat-pengantar/verifikasi/{$letter->qr_verification_code}");
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee($letter->letter_number);
        $verifyResponse->assertSee('RUKUN TETANGGA 04 / RUKUN WARGA 02');
    }

    public function test_login_page_renders_promotional_highlights_and_super_admin(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Transformasi Digital Lingkungan RT/RW');
        $response->assertSee('Smart Barrier Gate');
        $response->assertSee('Fabian (Super Admin Platform)');
    }

    public function test_registration_form_redirects_to_login_with_promo_credentials(): void
    {
        $complexName = 'Perumahan Grand Galaxy ' . rand(100, 999);
        $response = $this->post('/register-tenant', [
            'complex_name' => $complexName,
            'rt_rw' => 'RT 01 / RW 09',
            'city' => 'Bekasi',
            'contact_name' => 'Bpk. Hendra Gunawan',
            'contact_phone' => '081299887766',
            'estimated_houses' => 120,
            'need_barrier_gate' => 1,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('tenants', ['name' => $complexName]);
        $this->assertDatabaseHas('users', ['phone_number' => '081299887766', 'role' => 'ketua_rt']);

        $loginResponse = $this->followRedirects($response);
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Pendaftaran komplek');
    }

    public function test_dashboard_is_pure_operational_without_promo_registration(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Pengurus Lingkungan');
        $response->assertSee('Saldo Kas RT Aktif');
        $response->assertDontSee('Daftarkan Lingkungan Anda Hari Ini');
    }

    public function test_guest_sees_registration_form_on_landing_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Daftarkan Lingkungan Anda Hari Ini');
        $response->assertSee('KONSULTASI GRATIS & SURVEI PERANGKAT', false);
    }

    public function test_logged_in_user_does_not_see_registration_form_on_landing_page(): void
    {
        $user = User::where('role', 'super_admin')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Daftarkan Lingkungan Anda Hari Ini');
        $response->assertSee('Halo, ' . $user->name);
        $response->assertSee('Buka Dashboard Utama');
    }

    public function test_super_admin_hub_renders_with_multi_tenants_and_can_switch(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $this->assertNotNull($superAdmin);

        $response = $this->actingAs($superAdmin)->get('/super-admin');
        $response->assertStatus(200);
        $response->assertSee('PUSAT KENDALI PLATFORM', false);
        $response->assertSee('Cluster Melati Mas');
        $response->assertSee('Desa Sukamaju Guyub');

        $desaTenant = \App\Models\Tenant::where('environment_type', 'kampung')->first();
        $this->assertNotNull($desaTenant);

        $switchResponse = $this->actingAs($superAdmin)->post("/super-admin/switch-tenant/{$desaTenant->id}");
        $switchResponse->assertRedirect();
        $switchResponse->assertSessionHas('active_tenant_id', $desaTenant->id);
    }

    public function test_satpam_and_warga_are_redirected_from_dashboard_with_strict_rbac(): void
    {
        $satpam = User::where('role', 'satpam')->first();
        $this->assertNotNull($satpam);
        $satpamResponse = $this->actingAs($satpam)->get('/dashboard');
        $satpamResponse->assertRedirect(route('satpam.console'));

        $warga = User::where('role', 'warga')->first();
        $this->assertNotNull($warga);
        $wargaResponse = $this->actingAs($warga)->get('/dashboard');
        $wargaResponse->assertRedirect(route('warga.index'));
    }

    public function test_landing_page_is_standalone_without_sidebar_and_has_hardware_and_partnership(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('<aside', false);
        $response->assertDontSee('Laci Menu', false);

        // Hardware Ready & Coming Soon
        $response->assertSee('Smart Barrier Gate Otomatis');
        $response->assertSee('CCTV ANPR');
        $response->assertSee('Tiang Tombol Panik');
        $response->assertSee('Sensor Banjir');
        $response->assertSee('Smart PJU');
        $response->assertSee('Smart TPS');

        // Kontak & Kemitraan
        $response->assertSee('partnership@ewarga.id');
        $response->assertSee('sales@ewarga.id');
        $response->assertSee('+62 812-3456-7890');
    }

    public function test_ketua_rw_has_higher_supervisory_access_to_dashboard_and_letters(): void
    {
        $ketuaRw = User::where('role', 'ketua_rw')->first();
        $this->assertNotNull($ketuaRw, 'Akun Ketua RW harus ada di seeder');

        // Test Quick Login Ketua RW
        $quickResponse = $this->get('/quick-login/ketua_rw');
        $quickResponse->assertRedirect(route('dashboard'));

        // Test Dashboard Access
        $dashboardResponse = $this->actingAs($ketuaRw)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Supervisi Wilayah RW');
        $dashboardResponse->assertSee('SUPERVISI SELURUH RT');

        // Test Letters Access
        $lettersResponse = $this->actingAs($ketuaRw)->get('/surat-pengantar');
        $lettersResponse->assertStatus(200);
    }
}
