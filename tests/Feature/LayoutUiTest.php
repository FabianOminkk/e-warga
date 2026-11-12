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
}

