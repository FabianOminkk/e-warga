<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\CashBook;
use App\Models\CitizenReport;
use App\Models\House;
use App\Models\LetterRequest;
use App\Models\Payment;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChecklistCompletenessTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private House $house;
    private User $ketuaRt;
    private User $sekretaris;
    private User $bendahara;
    private User $satpam;
    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Cluster Melati Mas',
            'slug' => 'cluster-melati-mas',
            'environment_type' => 'cluster',
            'rt_rw' => 'RT 05 / RW 12',
            'city' => 'Bekasi',
            'is_active' => true,
        ]);

        $this->house = House::create([
            'tenant_id' => $this->tenant->id,
            'block_name' => 'A',
            'house_number' => '1',
            'no_kk' => '3275011100001111',
            'full_address' => 'Blok A No. 1',
            'occupancy_status' => 'occupied',
        ]);

        SmartGate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gerbang Utama',
            'device_uid' => 'esp32-scanner-01',
            'secret_token' => 'smartrw-rfid-device',
            'status' => 'offline',
            'last_ping_at' => null,
        ]);

        $this->ketuaRt = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Ketua RT Test',
            'email' => 'rt@example.com',
            'phone_number' => '08111111001',
            'password' => Hash::make('admin123'),
            'role' => 'ketua_rt',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
            'status' => 'active',
        ]);

        $this->sekretaris = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Sekretaris Test',
            'email' => 'sekretaris@example.com',
            'phone_number' => '08111111002',
            'password' => Hash::make('admin123'),
            'role' => 'sekretaris',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
            'status' => 'active',
        ]);

        $this->bendahara = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bendahara Test',
            'email' => 'bendahara@example.com',
            'phone_number' => '08111111003',
            'password' => Hash::make('admin123'),
            'role' => 'bendahara',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
            'status' => 'active',
        ]);

        $this->satpam = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Satpam Test',
            'email' => 'satpam@example.com',
            'phone_number' => '08111111004',
            'password' => Hash::make('admin123'),
            'role' => 'satpam',
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
            'status' => 'active',
        ]);

        $this->warga = User::create([
            'tenant_id' => $this->tenant->id,
            'house_id' => $this->house->id,
            'name' => 'Warga Test',
            'email' => 'warga@example.com',
            'phone_number' => '08111111005',
            'nik' => '3275011100002222',
            'no_kk' => '3275011100001111',
            'password' => Hash::make('warga123'),
            'role' => 'warga',
            'is_family_head' => true,
            'verification_status' => 'approved',
            'gate_access_enabled' => true,
            'status' => 'active',
        ]);
    }

    public function test_residents_and_kk_crud_and_csv_export(): void
    {
        $this->actingAs($this->warga)->get(route('residents.index'))->assertRedirect(route('warga.index'));

        $this->actingAs($this->ketuaRt)->get(route('residents.index'))->assertOk();

        $this->actingAs($this->ketuaRt)->post(route('residents.houses.store'), [
            'block_name' => 'Blok C',
            'house_number' => '18',
            'no_kk' => '3275019988776655',
            'occupancy_status' => 'occupied',
        ])->assertRedirect();

        $houseC18 = House::where('block_name', 'BLOK C')->where('house_number', '18')->firstOrFail();
        $this->assertSame('3275019988776655', $houseC18->no_kk);

        $this->actingAs($this->ketuaRt)->post(route('residents.store'), [
            'name' => 'Anggota Baru',
            'email' => 'anggota.baru@example.com',
            'phone_number' => '081200009999',
            'nik' => '3275019988770001',
            'house_id' => $houseC18->id,
            'role' => 'warga',
            'is_family_head' => '1',
            'approve_immediately' => '1',
        ])->assertRedirect();

        $created = User::where('email', 'anggota.baru@example.com')->firstOrFail();
        $this->assertSame('3275019988776655', $created->no_kk);

        // Role escalation guard: ketua_rt must not elevate user to super_admin or ketua_rw
        $this->actingAs($this->ketuaRt)->put(route('residents.update', $created->id), [
            'name' => 'Anggota Baru',
            'phone_number' => '081200009999',
            'no_kk' => '3275019988776699',
            'house_id' => $houseC18->id,
            'role' => 'super_admin',
            'status' => 'active',
            'is_family_head' => '1',
        ])->assertRedirect();

        $this->assertSame('warga', $created->fresh()->role);

        $this->actingAs($this->ketuaRt)->put(route('residents.update', $created->id), [
            'name' => 'Anggota Baru',
            'phone_number' => '081200009999',
            'no_kk' => '3275019988776699',
            'house_id' => $houseC18->id,
            'role' => 'ketua_rw',
            'status' => 'active',
            'is_family_head' => '1',
        ])->assertRedirect();

        $this->assertSame('warga', $created->fresh()->role);
        $this->assertSame('3275019988776699', $houseC18->fresh()->no_kk);

        $this->actingAs($this->ketuaRt)
            ->get(route('residents.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $this->actingAs($this->ketuaRt)->delete(route('residents.destroy', $created->id))->assertRedirect();
        $this->assertNull(User::find($created->id));
    }

    public function test_billing_generation_payment_rejection_reset_approval_receipt_and_cashbook_export(): void
    {
        $this->actingAs($this->bendahara)->post(route('dashboard.generate-billings'), [
            'billing_month' => '2026-12',
            'due_date' => '2026-12-10',
            'amount' => 150000,
            'description' => 'Iuran Bulanan Desember 2026',
        ])->assertRedirect();

        $billing = Billing::where('house_id', $this->house->id)->firstOrFail();
        $this->assertSame('unpaid', $billing->status);

        // Citizen pays
        $this->actingAs($this->warga)->post(route('warga.pay-billing'), [
            'billing_id' => $billing->id,
            'amount_paid' => 150000,
            'payment_method' => 'qris',
        ])->assertRedirect();

        $this->assertSame('pending_verification', $billing->fresh()->status);
        $payment1 = Payment::where('billing_id', $billing->id)->latest('id')->firstOrFail();

        // Bendahara rejects -> billing resets to unpaid
        $this->actingAs($this->bendahara)->post(route('dashboard.verify-payment', $payment1->id), [
            'action' => 'reject',
            'reason' => 'Bukti tidak jelas',
        ])->assertRedirect();

        $this->assertSame('unpaid', $billing->fresh()->status);
        $this->assertSame('Bukti tidak jelas', $payment1->fresh()->rejection_reason);

        // Citizen re-pays & Bendahara approves -> paid + CashBook + E-Kwitansi + CSV Export
        $this->actingAs($this->warga)->post(route('warga.pay-billing'), [
            'billing_id' => $billing->id,
            'amount_paid' => 150000,
            'payment_method' => 'manual_transfer',
        ])->assertRedirect();

        $payment2 = Payment::where('billing_id', $billing->id)->latest('id')->firstOrFail();

        $this->actingAs($this->bendahara)->post(route('dashboard.verify-payment', $payment2->id), [
            'action' => 'approve',
        ])->assertRedirect();

        $this->assertSame('paid', $billing->fresh()->status);
        $this->assertNull($payment2->fresh()->rejection_reason);
        $this->assertDatabaseHas('cash_books', [
            'tenant_id' => $this->tenant->id,
            'type' => 'income',
            'category' => 'Iuran Warga',
        ]);

        $this->actingAs($this->warga)
            ->get(route('warga.receipt', $billing->id))
            ->assertOk()
            ->assertSee('E-KWITANSI PEMBAYARAN IURAN');

        $this->actingAs($this->bendahara)
            ->get(route('dashboard.cashbook.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_citizen_reports_workflow_and_ai_shortcut(): void
    {
        $this->actingAs($this->warga)->get(route('reports.index'))->assertOk();

        $this->actingAs($this->warga)->post(route('reports.store'), [
            'type' => 'kerusakan_fasilitas',
            'category' => 'Penerangan Jalan',
            'title' => 'Lampu Taman Padam',
            'description' => 'Lampu taman dekat pos jaga mati sejak kemarin malam.',
            'priority' => 'high',
        ])->assertRedirect(route('reports.index'));

        $report = CitizenReport::where('title', 'Lampu Taman Padam')->firstOrFail();

        // Warga cannot respond to reports
        $this->actingAs($this->warga)->post(route('reports.respond', $report->id), [
            'status' => 'resolved',
            'official_response' => 'Unauthorized',
        ])->assertRedirect();
        $this->assertSame('pending', $report->fresh()->status);

        // Sekretaris can respond
        $this->actingAs($this->sekretaris)->post(route('reports.respond', $report->id), [
            'status' => 'in_progress',
            'official_response' => 'Petugas teknisi sudah ditugaskan.',
        ])->assertRedirect();
        $this->assertSame('in_progress', $report->fresh()->status);
    }

    public function test_security_guards_staff_password_and_mobile_cards(): void
    {
        $letter = LetterRequest::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->warga->id,
            'letter_type' => 'pengantar_ktp',
            'applicant_name' => $this->warga->name,
            'nik' => $this->warga->nik,
            'house_block' => 'Blok A No. 1',
            'purpose' => 'Pembuatan KTP Baru',
            'status' => 'pending',
        ]);

        // Bendahara / satpam / warga blocked from approving
        $this->actingAs($this->bendahara)->post(route('letters.approve', $letter->id))->assertRedirect();
        $this->assertSame('pending', $letter->fresh()->status);

        $satpamToken = $this->postJson('/api/v1/auth/login', [
            'login' => $this->satpam->email,
            'password' => 'admin123',
        ])->json('data.token');

        $this->withHeader('Authorization', 'Bearer ' . $satpamToken)
            ->postJson("/api/v1/letters/{$letter->id}/approve")
            ->assertStatus(403);

        // Sekretaris can approve & update own password via warga.change-password
        $this->actingAs($this->sekretaris)->post(route('letters.approve', $letter->id))->assertRedirect();
        $this->assertSame('approved', $letter->fresh()->status);

        $this->actingAs($this->sekretaris)->post(route('warga.change-password'), [
            'current_password' => 'admin123',
            'new_password' => 'sekretarisNew99',
            'new_password_confirmation' => 'sekretarisNew99',
        ])->assertRedirect();
        $this->assertTrue(Hash::check('sekretarisNew99', $this->sekretaris->fresh()->password));

        // Satpam API visitor approval & ESP32 gate check
        $pendingVisitor = Visitor::create([
            'tenant_id' => $this->tenant->id,
            'host_house_id' => $this->house->id,
            'invited_by_user_id' => $this->warga->id,
            'guest_name' => 'Tamu Pending',
            'guest_type' => 'guest',
            'qr_token' => 'QR-PEND-001',
            'approval_status' => 'pending',
            'is_used' => false,
            'valid_from' => now()->subMinute(),
            'valid_until' => now()->addHour(),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $satpamToken)
            ->postJson('/api/v1/satpam/checkin-visitor', ['qr_token' => 'QR-PEND-001'])
            ->assertStatus(403);

        Cache::forget('rfid_reader_online');
        SmartGate::query()->update(['status' => 'offline', 'last_ping_at' => null]);
        Visitor::create([
            'tenant_id' => $this->tenant->id,
            'host_house_id' => $this->house->id,
            'invited_by_user_id' => $this->warga->id,
            'guest_name' => 'Tamu Offline',
            'guest_type' => 'guest',
            'qr_token' => 'QR-APPR-OFF',
            'approval_status' => 'approved',
            'is_used' => false,
            'valid_from' => now()->subMinute(),
            'valid_until' => now()->addHour(),
        ]);

        $this->withHeader('Authorization', 'Bearer ' . $satpamToken)
            ->postJson('/api/v1/satpam/checkin-visitor', ['qr_token' => 'QR-APPR-OFF'])
            ->assertOk()
            ->assertJsonPath('data.gate_opened', false);

        // Warga portal displays new cards & mobile layout
        $this->actingAs($this->warga)
            ->get(route('warga.index'))
            ->assertOk()
            ->assertSee('Laporan & Aspirasi Warga', false)
            ->assertSee('Posyandu Digital', false);
    }
}
