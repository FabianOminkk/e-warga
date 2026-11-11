<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\LetterRequest;
use App\Models\PosyanduMember;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestApiArchitectureTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private SmartGate $smartGate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'              => 'Komplek Harmoni Indah',
            'slug'              => 'harmoni-indah',
            'rt_rw'             => 'RT 01 / RW 05',
            'province'          => 'DKI Jakarta',
            'city'              => 'Jakarta Selatan',
            'district'          => 'Kebayoran Baru',
            'postal_code'       => '12110',
            'environment_type'  => 'cluster',
            'is_active'         => true,
        ]);

        $house = House::create([
            'tenant_id'        => $this->tenant->id,
            'block_name'       => 'Blok A',
            'house_number'     => '01',
            'occupancy_status' => 'occupied',
        ]);

        $this->user = User::create([
            'tenant_id'           => $this->tenant->id,
            'house_id'            => $house->id,
            'name'                => 'Ahmad Developer',
            'email'               => 'ahmad@example.com',
            'phone_number'        => '081234567890',
            'password'            => Hash::make('password123'),
            'role'                => 'warga',
            'verification_status' => 'approved',
            'status'              => 'active',
        ]);

        $this->smartGate = SmartGate::create([
            'tenant_id'    => $this->tenant->id,
            'name'         => 'Gerbang Utama',
            'device_uid'   => 'ESP32-GATE-TEST',
            'secret_token' => 'SECRET-TEST-TOKEN',
            'status'       => 'online',
        ]);
    }

    public function test_api_login_returns_token_and_user_profile(): void
    {
        $response = $this->postJson(route('api.auth.login'), [
            'login'    => 'ahmad@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);
    }

    public function test_protected_endpoint_rejects_unauthenticated_request(): void
    {
        $response = $this->getJson(route('api.auth.me'));
        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_protected_endpoints_succeed_with_bearer_token(): void
    {
        // 1. Login to get token
        $loginRes = $this->postJson(route('api.auth.login'), [
            'login'    => 'ahmad@example.com',
            'password' => 'password123',
        ]);

        $token = $loginRes->json('data.token');
        $headers = ['Authorization' => "Bearer {$token}"];

        // 2. Test /api/v1/auth/me
        $meRes = $this->getJson(route('api.auth.me'), $headers);
        $meRes->assertStatus(200)
            ->assertJsonPath('data.name', 'Ahmad Developer');

        // 3. Test /api/v1/dashboard/summary
        $summaryRes = $this->getJson(route('api.dashboard.summary'), $headers);
        $summaryRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['statistics', 'tenant']]);

        // 4. Test /api/v1/warga/portal
        $portalRes = $this->getJson(route('api.warga.portal'), $headers);
        $portalRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 5. Test /api/v1/warga/open-gate
        $gateRes = $this->postJson(route('api.warga.open-gate'), [], $headers);
        $gateRes->assertStatus(200)
            ->assertJsonPath('data.gate_name', 'Gerbang Utama');

        // 6. Test /api/v1/warga/visitors (Create Visitor)
        $visitorRes = $this->postJson(route('api.warga.visitors.create'), [
            'guest_name'    => 'Pak Kurir J&T',
            'phone_number'  => '0899887766',
            'vehicle_plate' => 'B 1234 XYZ',
            'guest_type'    => 'courier',
        ], $headers);
        $visitorRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.guest_name', 'Pak Kurir J&T');

        // 7. Test /api/v1/satpam/trigger-manual
        $satpamRes = $this->postJson(route('api.satpam.trigger-manual'), [
            'reason' => 'Uji Coba Pos Satpam',
        ], $headers);
        $satpamRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 8. Test /api/v1/schedules (Jadwal Keamanan)
        $scheduleRes = $this->getJson(route('api.schedules.index'), $headers);
        $scheduleRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 9. Test /api/v1/posyandu (Posyandu Digital)
        $posyanduStore = $this->postJson(route('api.posyandu.members.store'), [
            'category'    => 'balita',
            'name'        => 'Adik Kenzi',
            'gender'      => 'L',
            'birth_date'  => '2024-05-10',
            'parent_name' => 'Ahmad Developer',
        ], $headers);
        $posyanduStore->assertStatus(201)
            ->assertJsonPath('data.name', 'Adik Kenzi');

        $posyanduList = $this->getJson(route('api.posyandu.index'), $headers);
        $posyanduList->assertStatus(200)
            ->assertJsonPath('success', true);

        // 10. Test /api/v1/letters (Surat Pengantar RT)
        $letterStore = $this->postJson(route('api.letters.store'), [
            'letter_type'    => 'pengantar_ktp',
            'applicant_name' => 'Ahmad Developer',
            'nik'            => '3201123456789001',
            'house_block'    => 'Blok A No 01',
            'purpose'        => 'Pembuatan KTP Elektronik Baru',
        ], $headers);
        $letterStore->assertStatus(201)
            ->assertJsonPath('success', true);

        $verifyCode = $letterStore->json('data.qr_verification_code');

        // 11. Test Public Letter Verification
        $verifyRes = $this->getJson(route('api.letters.verify', ['code' => $verifyCode]));
        $verifyRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 12. Test Logout
        $logoutRes = $this->postJson(route('api.auth.logout'), [], $headers);
        $logoutRes->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
