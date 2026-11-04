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

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Pengurus Lingkungan');
    }

    public function test_satpam_console_renders_successfully(): void
    {
        $response = $this->get('/satpam');
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
        $response = $this->get('/warga');
        $response->assertStatus(200);
        $response->assertSee('BUKA GERBANG PORTAL');
    }
}
