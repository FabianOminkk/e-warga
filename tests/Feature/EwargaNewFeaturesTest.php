<?php

namespace Tests\Feature;

use App\Models\LetterRequest;
use App\Models\PosyanduMember;
use App\Models\PosyanduRecord;
use App\Models\SecuritySchedule;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EwargaNewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_access_jadwal_keamanan_page(): void
    {
        $response = $this->get('/jadwal-keamanan');
        $response->assertStatus(200);
        $response->assertSee('Jadwal Keamanan');
        $response->assertSee('Pak Slamet');
    }

    public function test_theme_switcher_rendered_in_layout(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('localStorage.getItem(\'theme\')', false);
        $response->assertSee('Toggle Dark / Light Theme');
    }

    public function test_can_toggle_environment_mode(): void
    {
        $tenant = Tenant::first();
        $initialMode = $tenant->environment_type;

        $response = $this->post('/toggle-mode');
        $response->assertRedirect();

        $tenant->refresh();
        $this->assertNotEquals($initialMode, $tenant->environment_type);
    }

    public function test_can_checkin_security_schedule(): void
    {
        $schedule = SecuritySchedule::where('status', 'scheduled')->first();
        $this->assertNotNull($schedule);

        $response = $this->post("/jadwal-keamanan/{$schedule->id}/checkin");
        $response->assertRedirect();

        $schedule->refresh();
        $this->assertEquals('present', $schedule->status);
        $this->assertNotNull($schedule->checked_in_at);
    }

    public function test_can_access_posyandu_page(): void
    {
        $response = $this->get('/posyandu');
        $response->assertStatus(200);
        $response->assertSee('Modul Posyandu Digital RT/RW');
        $response->assertSee('Balita');
        $response->assertSee('Rayyan Al-Fatih');
    }

    public function test_can_register_new_posyandu_member(): void
    {
        $response = $this->post('/posyandu/members', [
            'name' => 'Fathan Al-Ghifari',
            'category' => 'balita',
            'gender' => 'L',
            'birth_date' => '2025-06-15',
            'family_card_number' => '3216012903820099',
            'nik' => '3216011506250001',
            'parent_name' => 'Bpk. Ahmad & Ibu Siti',
            'address' => 'Blok E No. 01',
            'phone' => '081234567899',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posyandu_members', [
            'name' => 'Fathan Al-Ghifari',
            'category' => 'balita',
        ]);
    }

    public function test_can_record_posyandu_checkup(): void
    {
        $member = PosyanduMember::where('category', 'balita')->first();
        $this->assertNotNull($member);

        $response = $this->post('/posyandu/records', [
            'posyandu_member_id' => $member->id,
            'checkup_date' => date('Y-m-d'),
            'weight_kg' => 12.5,
            'height_cm' => 88.0,
            'vitamins_or_vaccines' => 'Vitamin A Kapsul Biru',
            'examiner_name' => 'Bidan Nina',
            'action_plan' => 'Gizi baik, perkembangan optimal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posyandu_records', [
            'posyandu_member_id' => $member->id,
            'weight_kg' => 12.5,
        ]);
    }

    public function test_can_access_letters_page(): void
    {
        $response = $this->get('/surat-pengantar');
        $response->assertStatus(200);
        $response->assertSee('Layanan Surat Pengantar RT Online');
    }

    public function test_can_request_rt_letter(): void
    {
        $response = $this->post('/surat-pengantar', [
            'letter_type' => 'pengantar_kk',
            'applicant_name' => 'Bambang Pamungkas',
            'nik' => '3216012903820001',
            'house_block' => 'Blok C No. 12',
            'purpose' => 'Penambahan anggota keluarga baru pada kartu keluarga',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('letter_requests', [
            'letter_type' => 'pengantar_kk',
            'applicant_name' => 'Bambang Pamungkas',
            'status' => 'pending',
        ]);
    }

    public function test_can_approve_letter_and_verify_qr_code(): void
    {
        $letter = LetterRequest::where('status', 'pending')->first();
        $this->assertNotNull($letter);

        $response = $this->post("/surat-pengantar/{$letter->id}/approve");
        $response->assertRedirect();

        $letter->refresh();
        $this->assertEquals('approved', $letter->status);
        $this->assertNotNull($letter->letter_number);
        $this->assertNotNull($letter->qr_verification_code);

        // Test public verification endpoint
        $verifyResponse = $this->get("/surat-pengantar/verifikasi/{$letter->qr_verification_code}");
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee($letter->letter_number);
        $verifyResponse->assertSee('RUKUN TETANGGA 04 / RUKUN WARGA 02');
    }
}
