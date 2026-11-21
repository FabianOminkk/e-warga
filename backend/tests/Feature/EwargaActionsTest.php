<?php

namespace Tests\Feature;

use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EwargaActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_warga_can_open_gate(): void
    {
        $warga = User::where('role', 'warga')->first();

        $response = $this->actingAs($warga)->post('/warga/open-gate');
        $response->assertSessionHas('success');
    }

    public function test_warga_can_create_visitor_pass(): void
    {
        $warga = User::where('role', 'warga')->first();

        $response = $this->actingAs($warga)->post('/warga/create-visitor', [
            'guest_name' => 'Kurir Paket Kilat',
            'guest_type' => 'courier',
            'vehicle_plate' => 'B 9999 XYZ',
            'duration_hours' => 2,
        ]);

        $response->assertSessionHas('new_visitor');
        $this->assertDatabaseHas('visitors', [
            'guest_name' => 'Kurir Paket Kilat',
            'is_used' => false,
        ]);
    }

    public function test_satpam_can_trigger_manual_open(): void
    {
        $satpam = User::where('role', 'satpam')->first();

        $response = $this->actingAs($satpam)->post('/satpam/trigger-manual', [
            'reason' => 'Buka Manual Darurat',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('gate_logs', [
            'reason' => 'Buka Manual Darurat',
            'access_method' => 'satpam_manual',
        ]);
    }

    public function test_satpam_can_checkin_valid_visitor(): void
    {
        $visitor = Visitor::where('is_used', false)->first();
        $satpam = User::where('role', 'satpam')->first();

        $response = $this->actingAs($satpam)->post('/satpam/checkin-visitor', [
            'qr_token' => $visitor->qr_token,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('visitors', [
            'id' => $visitor->id,
            'is_used' => true,
        ]);
    }

    public function test_simulator_can_be_triggered_via_ajax(): void
    {
        $response = $this->postJson('/simulator/trigger', [
            'method' => 'mobile_button',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'hold_duration_seconds' => 5,
        ]);
    }

    public function test_public_can_register_new_housing_complex(): void
    {
        $response = $this->post('/register-tenant', [
            'complex_name' => 'Cluster Permata Hijau',
            'rt_rw' => 'RT 02 / RW 04',
            'city' => 'Jakarta Selatan',
            'contact_name' => 'Bpk. Ahmad Fauzi',
            'contact_phone' => '081299988877',
            'estimated_houses' => 80,
            'need_barrier_gate' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tenants', [
            'name' => 'Cluster Permata Hijau',
        ]);
    }
}

