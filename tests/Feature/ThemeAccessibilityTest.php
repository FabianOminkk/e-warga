<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_theme_accessibility_switcher(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Standar (Emerald)');
        $response->assertSee('Ramah Buta Warna');
        $response->assertSee('Kontras Tinggi');
    }

    public function test_authenticated_dashboard_renders_theme_accessibility_switcher(): void
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
        $response->assertSee('Standar (Emerald)');
        $response->assertSee('Ramah Buta Warna');
        $response->assertSee('Kontras Tinggi');
        $response->assertSee('Protanopia / Deuteranopia');
    }
}
