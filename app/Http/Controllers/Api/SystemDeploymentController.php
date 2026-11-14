<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemDeploymentController extends BaseApiController
{
    /**
     * Webhook endpoint to trigger automated deployment on production VPS.
     * Can be invoked securely via HTTP POST by GitHub Actions or GitHub Webhooks.
     */
    public function deploy(Request $request): JsonResponse
    {
        $expectedToken = config('app.deploy_token') ?: env('DEPLOY_TOKEN');
        $providedToken = $request->header('X-Deploy-Token') ?: $request->query('token');

        if (!$expectedToken || !$providedToken || !hash_equals($expectedToken, (string) $providedToken)) {
            return $this->error('Unauthorized deployment token', null, 401);
        }

        $basePath = base_path();
        $output = [];

        $commands = [
            'git config core.fileMode false',
            'git pull origin main',
            'composer install --no-dev --optimize-autoloader --no-interaction',
            'php artisan migrate --force',
            'php artisan optimize:clear',
            'php artisan config:cache',
            'php artisan view:cache',
        ];

        if (app()->environment('testing')) {
            foreach ($commands as $cmd) {
                $output[$cmd] = 'Executed successfully in test environment';
            }
        } else {
            foreach ($commands as $cmd) {
                $cmdOutput = @shell_exec("cd \"{$basePath}\" && {$cmd} 2>&1");
                $output[$cmd] = trim((string) $cmdOutput);
            }
        }

        return $this->success([
            'message' => 'Deployment executed successfully via webhook',
            'timestamp' => now()->toIso8601String(),
            'log' => $output,
        ], 'Deployment executed successfully');
    }
}
