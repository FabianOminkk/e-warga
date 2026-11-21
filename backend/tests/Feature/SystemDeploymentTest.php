<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemDeploymentTest extends TestCase
{
    public function test_deploy_webhook_rejects_unauthorized_requests(): void
    {
        $response = $this->postJson('/api/v1/system/deploy-webhook');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized deployment token',
            ]);
    }

    public function test_deploy_webhook_accepts_valid_token_header(): void
    {
        config(['app.deploy_token' => 'test-deploy-secret-token']);

        $response = $this->withHeaders([
            'X-Deploy-Token' => 'test-deploy-secret-token',
        ])->postJson('/api/v1/system/deploy-webhook');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'message',
                    'timestamp',
                    'log',
                ],
            ]);
    }
}

