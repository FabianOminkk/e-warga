<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EwargaPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('E-WARGA');
    }

    public function test_unauthenticated_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/warga')->assertRedirect('/login');
        $this->get('/satpam')->assertRedirect('/login');
        $this->get('/posyandu')->assertRedirect('/login');
        $this->get('/surat-pengantar')->assertRedirect('/login');
        $this->get('/jadwal-keamanan')->assertRedirect('/login');
    }

    public function test_dashboard_renders_successfully(): void
    {
        $admin = \App\Models\User::where('role', 'ketua_rt')->first() 
            ?? \App\Models\User::where('role', 'pengurus_rt')->first();
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Pengurus Lingkungan');
    }

    public function test_satpam_console_renders_successfully(): void
    {
        $satpam = \App\Models\User::where('role', 'satpam')->first();
        $response = $this->actingAs($satpam)->get('/satpam');
        $response->assertStatus(200);
        $response->assertSee('Console Pos Jaga');
    }

    public function test_simulator_renders_successfully(): void
    {
        $response = $this->get('/simulator');
        $response->assertStatus(200);
        $response->assertSee('Virtual Smart Gate Simulator');
    }

    public function test_warga_mobile_view_renders_successfully(): void
    {
        $warga = \App\Models\User::where('role', 'warga')->first();
        $response = $this->actingAs($warga)->get('/warga');
        $response->assertStatus(200);
        $response->assertSee('BUKA GERBANG PORTAL');
    }

    public function test_superadmin_hub_page_renders_successfully(): void
    {
        $superAdmin = \App\Models\User::where('role', 'super_admin')->first();
        $response = $this->actingAs($superAdmin)->get('/super-admin');
        $response->assertStatus(200);
        $response->assertSee('Pusat Pengelolaan Seluruh Wilayah');
    }

    public function test_unknown_url_renders_friendly_coming_soon_404_page(): void
    {
        $response = $this->get('/fitur-belum-ada-xyz');
        $response->assertStatus(404);
        $response->assertSee('Fitur Sedang Dalam Pengembangan (Coming Soon)');
        $response->assertSee('Maaf, Halaman Ini Belum Tersedia');
    }

    public function test_keuangan_page_separates_finance_recording_and_idle_timeout_logs_out(): void
    {
        $bendahara = \App\Models\User::where('role', 'bendahara')->firstOrFail();

        $dash = $this->actingAs($bendahara)->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('Analisis Akurat Aliran Kas & Kepatuhan Iuran', false);
        $dash->assertDontSee('id="seksi-keuangan"', false);

        $keuangan = $this->actingAs($bendahara)->get('/keuangan');
        $keuangan->assertStatus(200);
        $keuangan->assertSee('Keuangan &amp; Iuran RT', false);
        $keuangan->assertSee('id="seksi-keuangan"', false);
        $keuangan->assertSee('Antrian Verifikasi Pembayaran Iuran');
        $keuangan->assertSee('Catat Transaksi Kas RT');
        $keuangan->assertSee('Buku Kas Terbuka RT');

        // Login POST & idle timeout enforcement
        $loginRes = $this->post('/login', [
            'login' => $bendahara->email,
            'password' => 'admin123',
        ]);
        $loginRes->assertRedirect('/dashboard');
        $loginRes->assertSessionHas('success');
        $this->assertStringContainsString('Berhasil login!', (string) session('success'));

        // Simulate expired idle timestamp exceeding configured idle_timeout
        $timeoutSeconds = max(1, (int) config('session.idle_timeout', 15)) * 60;
        session(['last_activity_at' => now()->timestamp - $timeoutSeconds - 30]);

        $expiredRes = $this->get('/keuangan');
        $expiredRes->assertRedirect('/login');
        $expiredRes->assertSessionHas('warning');
        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.idle_timeout',
            'user_id' => $bendahara->id,
        ]);
    }
}
