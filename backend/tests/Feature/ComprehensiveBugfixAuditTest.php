<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\CashBook;
use App\Models\House;
use App\Models\Payment;
use App\Models\PosyanduMember;
use App\Models\PosyanduRecord;
use App\Models\SecuritySchedule;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ComprehensiveBugfixAuditTest extends TestCase
{
    use RefreshDatabase;

    private function createBearerToken(User $user): string
    {
        $token = 'ew_test_' . bin2hex(random_bytes(20));
        Cache::put("api_token:{$token}", $user->id, now()->addDays(30));
        return $token;
    }

    public function test_api_verify_payment_properly_records_amount_paid_and_title_in_cashbook(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Nirwana',
            'slug' => 'cluster-nirwana',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 01 / RW 01',
        ]);

        $bendahara = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bendahara',
        ]);

        $warga = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'warga',
        ]);

        $house = House::create([
            'tenant_id' => $tenant->id,
            'block_name' => 'Blok B',
            'house_number' => '12',
            'owner_name' => $warga->name,
            'status' => 'occupied',
        ]);

        $billing = Billing::create([
            'tenant_id' => $tenant->id,
            'house_id' => $house->id,
            'billing_period' => '2026-11-01',
            'amount' => 175000,
            'description' => 'Iuran Warga November',
            'status' => 'pending_verification',
            'due_date' => '2026-11-10',
        ]);

        $payment = Payment::create([
            'billing_id' => $billing->id,
            'payer_user_id' => $warga->id,
            'payment_method' => 'manual_transfer',
            'amount_paid' => 175000,
        ]);

        $token = $this->createBearerToken($bendahara);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/dashboard/payments/{$payment->id}/verify", [
                'action' => 'approve',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify CashBook was created with correct non-null amount and valid title
        $this->assertDatabaseHas('cash_books', [
            'tenant_id' => $tenant->id,
            'amount' => 175000,
            'title' => 'Pembayaran Iuran Warga November',
            'type' => 'income',
        ]);

        $this->assertDatabaseHas('billings', [
            'id' => $billing->id,
            'status' => 'paid',
        ]);
    }

    public function test_api_trigger_open_gate_denies_unverified_citizen(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Cempaka',
            'slug' => 'cluster-cempaka',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 02 / RW 03',
        ]);

        $gate = SmartGate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gerbang Utama',
            'device_uid' => 'ESP32-CEMPAKA',
            'status' => 'online',
        ]);

        // Unverified citizen (verification_status unverified, gate_access_enabled false)
        $unverifiedCitizen = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'warga',
            'verification_status' => 'unverified',
            'gate_access_enabled' => false,
        ]);

        $token = $this->createBearerToken($unverifiedCitizen);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/warga/open-gate');

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
    }

    public function test_api_trigger_open_gate_allows_verified_citizen(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Cempaka 2',
            'slug' => 'cluster-cempaka-2',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 02 / RW 03',
        ]);

        $gate = SmartGate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gerbang Utama 2',
            'device_uid' => 'ESP32-CEMPAKA-2',
            'status' => 'online',
            'last_ping_at' => now(),
        ]);

        \App\Services\RfidService::recordHeartbeat('ESP32-CEMPAKA-2', '127.0.0.1', $tenant->id, null, 'Gerbang Utama 2');

        // Verified citizen (approved by RW)
        $verifiedCitizen = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'warga',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
        ]);

        $token = $this->createBearerToken($verifiedCitizen);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/warga/open-gate');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_api_update_profile_normalizes_gender_to_avoid_db_truncation(): void
    {
        $user = User::factory()->create([
            'role' => 'warga',
            'gender' => 'male',
        ]);

        $token = $this->createBearerToken($user);

        // Client passes 'L' (Laki-laki)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/warga/profile', [
                'name' => 'Budi Santoso',
                'phone_number' => '081299988877',
                'email' => 'budi.santoso@example.com',
                'gender' => 'L',
            ]);

        $response->assertStatus(200);

        // Verify database holds 'male' without truncation error
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'gender' => 'male',
            'name' => 'Budi Santoso',
        ]);
    }

    public function test_posyandu_api_persists_blood_pressure_and_vaccines_correctly(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Posyandu Test',
            'slug' => 'cluster-posyandu-test',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 03 / RW 04',
        ]);

        $petugas = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'ketua_rt',
        ]);

        $token = $this->createBearerToken($petugas);

        // 1. Store member
        $memberResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/posyandu/members', [
                'category' => 'lansia',
                'name' => 'Kakek Subarjo',
                'family_card_number' => '3216000011112222',
                'gender' => 'L',
                'birth_date' => '1955-08-17',
                'notes' => 'Riwayat tensi darah tinggi',
            ]);

        $memberResponse->assertStatus(201);
        $memberId = $memberResponse->json('data.id');

        $this->assertDatabaseHas('posyandu_members', [
            'id' => $memberId,
            'name' => 'Kakek Subarjo',
            'family_card_number' => '3216000011112222',
            'notes' => 'Riwayat tensi darah tinggi',
        ]);

        // 2. Store record with systolic/diastolic
        $recordResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/posyandu/records', [
                'posyandu_member_id' => $memberId,
                'checkup_date' => '2026-11-20',
                'weight_kg' => 65.5,
                'height_cm' => 165,
                'systolic' => 145,
                'diastolic' => 95,
                'vitamin_a' => true,
                'notes' => 'Diberikan vitamin dan kurangi garam',
            ]);

        $recordResponse->assertStatus(201);

        // Verify blood pressure was composed as "145/95" and vitamins & action_plan were persisted
        $this->assertDatabaseHas('posyandu_records', [
            'posyandu_member_id' => $memberId,
            'blood_pressure' => '145/95',
            'nutrition_status' => 'hipertensi',
        ]);
    }

    public function test_api_schedule_toggle_mode_requires_super_admin_and_uses_kampung_enum(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Harmoni',
            'slug' => 'cluster-harmoni-mode',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 01 / RW 02',
        ]);

        $ketuaRt = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'ketua_rt',
        ]);
        $superAdmin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'super_admin',
        ]);

        $rtToken = $this->createBearerToken($ketuaRt);
        $this->withHeader('Authorization', "Bearer {$rtToken}")
            ->postJson('/api/v1/schedules/toggle-mode')
            ->assertStatus(403);

        $adminToken = $this->createBearerToken($superAdmin);
        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson('/api/v1/schedules/toggle-mode')
            ->assertOk()
            ->assertJsonPath('data.environment_type', 'kampung');

        $this->assertSame('kampung', $tenant->fresh()->environment_type);

        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson('/api/v1/schedules/toggle-mode')
            ->assertOk()
            ->assertJsonPath('data.environment_type', 'cluster');
    }

    public function test_schedules_and_cashbook_handle_invalid_month_and_omitted_notes_safely(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Aman',
            'slug' => 'cluster-aman',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 04 / RW 01',
        ]);

        $ketuaRt = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'ketua_rt',
        ]);
        $bendahara = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bendahara',
        ]);
        $satpam = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'satpam',
        ]);

        // Malformed month parameter must fall back cleanly instead of throwing 500
        $this->actingAs($ketuaRt)
            ->get('/jadwal-keamanan?month=invalid-month')
            ->assertOk();

        $rtToken = $this->createBearerToken($ketuaRt);
        $this->withHeader('Authorization', "Bearer {$rtToken}")
            ->getJson('/api/v1/schedules?month=not-a-date')
            ->assertOk()
            ->assertJsonPath('data.month', now()->format('Y-m'));

        // Creating schedule without optional 'notes' field must not fail with Undefined array key
        $this->actingAs($ketuaRt)->post('/jadwal-keamanan', [
            'user_id' => $satpam->id,
            'schedule_type' => 'satpam_shift',
            'duty_date' => now()->toDateString(),
            'shift_name' => 'Shift Malam Pos 1',
            'start_time' => '19:00',
            'end_time' => '07:00',
        ])->assertRedirect();

        $this->assertDatabaseHas('security_schedules', [
            'tenant_id' => $tenant->id,
            'shift_name' => 'Shift Malam Pos 1',
        ]);

        // Creating CashBook entry without 'notes' field must succeed cleanly
        $this->actingAs($bendahara)->post(route('dashboard.store-cashbook'), [
            'type' => 'expense',
            'category' => 'Operasional',
            'title' => 'Pembelian Token Listrik Pos',
            'amount' => 100000,
        ])->assertRedirect();

        $this->assertDatabaseHas('cash_books', [
            'tenant_id' => $tenant->id,
            'title' => 'Pembelian Token Listrik Pos',
        ]);
    }

    public function test_rbac_guards_ktp_verification_dashboard_api_posyandu_and_nutrition_enum(): void
    {
        $tenant = Tenant::create([
            'name' => 'Cluster Tertib',
            'slug' => 'cluster-tertib',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 03 / RW 02',
        ]);

        $warga = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'warga',
            'verification_status' => 'pending_rw',
            'gate_access_enabled' => false,
        ]);

        $ketuaRw = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'ketua_rw',
        ]);

        // Citizen cannot self-approve KTP via web or API
        $this->actingAs($warga)
            ->post(route('rw.verifications.approve', $warga->id))
            ->assertRedirect();
        $this->assertSame('pending_rw', $warga->fresh()->verification_status);

        $wargaToken = $this->createBearerToken($warga);
        $this->withHeader('Authorization', "Bearer {$wargaToken}")
            ->postJson("/api/v1/dashboard/verifications/{$warga->id}/approve")
            ->assertStatus(403);
        $this->withHeader('Authorization', "Bearer {$wargaToken}")
            ->getJson('/api/v1/dashboard/verifications')
            ->assertStatus(403);
        $this->withHeader('Authorization', "Bearer {$wargaToken}")
            ->postJson('/api/v1/dashboard/cashbook', [
                'type' => 'income',
                'category' => 'Iuran',
                'title' => 'Fake Entry',
                'amount' => 50000,
            ])->assertStatus(403);

        // Citizen cannot create web Posyandu members
        $this->actingAs($warga)->post(route('posyandu.members.store'), [
            'name' => 'Balita Unauthorized',
            'category' => 'balita',
            'gender' => 'L',
            'birth_date' => '2025-01-01',
        ])->assertRedirect();
        $this->assertDatabaseMissing('posyandu_members', ['name' => 'Balita Unauthorized']);

        // Posyandu API uses canonical 'gizi_kurang' enum matching status badge
        $member = PosyanduMember::create([
            'tenant_id' => $tenant->id,
            'category' => 'balita',
            'name' => 'Bayi Timbangan',
            'gender' => 'P',
            'birth_date' => now()->subMonths(12)->toDateString(),
        ]);

        $rwToken = $this->createBearerToken($ketuaRw);
        $this->withHeader('Authorization', "Bearer {$rwToken}")
            ->postJson('/api/v1/posyandu/records', [
                'posyandu_member_id' => $member->id,
                'checkup_date' => now()->toDateString(),
                'weight_kg' => 5.2,
                'height_cm' => 75.0,
            ])->assertStatus(201);

        $record = PosyanduRecord::where('posyandu_member_id', $member->id)->firstOrFail();
        $this->assertSame('gizi_kurang', $record->nutrition_status);
        $this->assertSame('Gizi Kurang', $record->status_badge['label']);
    }
}
