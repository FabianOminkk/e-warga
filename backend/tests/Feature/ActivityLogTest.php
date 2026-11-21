<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $superAdmin;
    protected User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Cluster Test Melati',
            'slug' => 'cluster-test-melati',
            'rt_rw' => 'RT 01 / RW 01',
            'city' => 'Jakarta Selatan',
            'environment_type' => 'cluster',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Super Administrator',
            'email' => 'superadmin@ewarga.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->warga = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Warga Percobaan',
            'email' => 'warga.test@ewarga.id',
            'password' => Hash::make('password123'),
            'role' => 'warga',
            'nik' => '3201234567890001',
            'status' => 'active',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
        ]);
    }

    public function test_activity_logger_service_records_log_and_redacts_sensitive_keys(): void
    {
        $log = ActivityLogger::log(
            'auth.test_action',
            'Melakukan pengujian pencatatan audit log',
            'warning',
            [
                'public_info' => 'Ini data umum',
                'password' => 'supersecretpassword123',
                'token' => 'sensitive_token_9999',
            ],
            $this->superAdmin
        );

        $this->assertNotNull($log);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.test_action',
            'severity' => 'warning',
            'actor_name' => 'Super Administrator',
            'actor_role' => 'super_admin',
        ]);

        // Pastikan password & token disanitasi (CWE-532 protection)
        $this->assertEquals('[REDACTED_SENSITIVE]', $log->properties['password']);
        $this->assertEquals('[REDACTED_SENSITIVE]', $log->properties['token']);
        $this->assertEquals('Ini data umum', $log->properties['public_info']);
    }

    public function test_failed_login_records_danger_activity_log(): void
    {
        $response = $this->post('/login', [
            'login' => 'intruder@unknown.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.failed',
            'severity' => 'danger',
        ]);
    }

    public function test_successful_login_records_info_activity_log(): void
    {
        $response = $this->post('/login', [
            'login' => 'warga.test@ewarga.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/warga');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.login',
            'severity' => 'info',
            'actor_name' => 'Warga Percobaan',
        ]);
    }

    public function test_super_admin_can_view_activity_logs_dashboard(): void
    {
        ActivityLogger::log('audit.sample', 'Sample audit event', 'info', [], $this->warga);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin');

        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas & Audit Keamanan Sistem', false);
        $response->assertSee('audit.sample');
        $response->assertSee('Warga Percobaan');
    }

    public function test_regular_warga_cannot_access_super_admin_dashboard(): void
    {
        $response = $this->actingAs($this->warga)->get('/super-admin');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('error');
    }

    public function test_super_admin_can_export_activity_logs_as_csv(): void
    {
        ActivityLogger::log('export.sample', 'Event yang akan diekspor', 'info', [], $this->superAdmin);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin/activity-logs/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        
        // Cek nama header kolom
        $this->assertStringContainsString('Kode Aksi', $response->streamedContent());
        $this->assertStringContainsString('export.sample', $response->streamedContent());
    }
}
