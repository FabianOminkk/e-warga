<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_clean_theme_toggle(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('localStorage.getItem(\'theme\')', false);
    }

    public function test_authenticated_dashboard_renders_theme_toggle_and_drawer(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Melati Mas',
            'slug' => 'cluster-melati-mas',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 05 / RW 12',
        ]);

        $user = User::factory()->create([
            'role' => 'pengurus_rt',
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Mode Gelap');
        $response->assertSee('Role');
    }

    public function test_mobile_responsive_elements_are_rendered(): void
    {
        // 1. Landing page mobile CTA & responsive classes
        $landingResponse = $this->get('/');
        $landingResponse->assertStatus(200);
        $landingResponse->assertSee('Konsultasi<span class="hidden sm:inline"> & Survei</span>', false);
        $landingResponse->assertSee('Tanya Bang Warga');

        // 2. Login page mobile order classes
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('order-1 lg:order-2', false);
        $loginResponse->assertSee('order-2 lg:order-1', false);

        // 3. Authenticated mobile dock on internal operational view
        $tenant = Tenant::create([
            'name' => 'Cluster Melati Mas',
            'slug' => 'cluster-melati-mas-mobile',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 05 / RW 12',
        ]);

        $user = User::factory()->create([
            'role' => 'pengurus_rt',
            'tenant_id' => $tenant->id,
        ]);

        $dashResponse = $this->actingAs($user)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Navigasi Cepat Smartphone Android');
        $dashResponse->assertSee('Tanya AI');
    }
}

