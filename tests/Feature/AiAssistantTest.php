<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_can_fetch_ai_suggestions(): void
    {
        $response = $this->getJson('/api/v1/ai/suggestions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'assistant_name' => 'Bang Warga AI',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'suggestions',
                    'assistant_name',
                    'role',
                ],
            ]);
    }

    public function test_chat_validation_requires_message(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_chat_responds_to_gate_queries(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Bagaimana cara membuka palang gerbang?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['reply']);
        $this->assertStringContainsString('palang', strtolower($data['reply']));
        $this->assertNotEmpty($data['actions']);
        $this->assertNotEmpty($data['suggestions']);
    }

    public function test_chat_responds_to_iuran_queries(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Cara bayar iuran kas bulanan?',
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertStringContainsString('iuran', strtolower($data['reply']));
        $this->assertStringContainsString('rekening', strtolower($data['reply']));
    }

    public function test_chat_responds_to_letters_queries(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Saya mau buat surat pengantar skck',
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertStringContainsString('surat', strtolower($data['reply']));
    }

    public function test_chat_responds_to_emergency_panic_queries(): void
    {
        $response = $this->postJson('/api/v1/ai/chat', [
            'message' => 'Ada kebakaran tolong darurat satpam!',
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertStringContainsString('darurat', strtolower($data['reply']));
    }

    public function test_web_route_chat_functions(): void
    {
        $user = User::where('role', 'warga')->first();

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Halo Bang Warga, siapa kamu?',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertStringContainsString('Bang Warga', $data['reply']);
    }
}

