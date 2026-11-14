<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class QuickLoginTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Cluster Test Melati',
            'slug' => 'cluster-test-melati',
            'rt_rw' => 'RT 01 / RW 01',
            'city' => 'Jakarta Selatan',
            'environment_type' => 'cluster',
        ]);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Fabian Super Admin',
            'email' => 'superadmin@ewarga.id',
            'role' => 'super_admin',
            'password' => bcrypt('admin123'),
        ]);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bpk. Sutrisno',
            'email' => 'ketuart@ewarga.id',
            'role' => 'ketua_rt',
            'password' => bcrypt('admin123'),
        ]);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pak Slamet',
            'email' => 'satpam@ewarga.id',
            'role' => 'satpam',
            'password' => bcrypt('admin123'),
        ]);

        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bpk. Bambang Pamungkas',
            'email' => 'bambang@warga.id',
            'role' => 'warga',
            'verification_status' => 'approved',
            'password' => bcrypt('admin123'),
        ]);
    }

    public function test_superadmin_quick_login_redirects_to_superadmin_hub(): void
    {
        $response = $this->get(route('quick.login', 'super_admin'));

        $response->assertRedirect(route('superadmin.index'));
        $this->assertTrue(Auth::check());
        $this->assertEquals('super_admin', Auth::user()->role);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.quick_login',
            'actor_role' => 'super_admin',
        ]);
    }

    public function test_ketua_rt_quick_login_redirects_to_dashboard(): void
    {
        $response = $this->get(route('quick.login', 'ketua_rt'));

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertEquals('ketua_rt', Auth::user()->role);
    }

    public function test_satpam_quick_login_redirects_to_satpam_console(): void
    {
        $response = $this->get(route('quick.login', 'satpam'));

        $response->assertRedirect(route('satpam.console'));
        $this->assertTrue(Auth::check());
        $this->assertEquals('satpam', Auth::user()->role);
    }

    public function test_warga_quick_login_redirects_to_warga_portal(): void
    {
        $response = $this->get(route('quick.login', 'warga'));

        $response->assertRedirect(route('warga.index'));
        $this->assertTrue(Auth::check());
        $this->assertEquals('warga', Auth::user()->role);
    }
}
