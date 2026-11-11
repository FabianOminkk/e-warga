<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends BaseApiController
{
    /**
     * Authenticate user and issue API Bearer Token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with(['tenant', 'house'])
            ->where('email', $validated['login'])
            ->orWhere('phone_number', $validated['login'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->error('Kredensial login tidak cocok. Periksa email/no HP dan kata sandi.', null, 401);
        }

        if ($user->status !== 'active') {
            return $this->error('Akun warga Anda sedang dinonaktifkan. Hubungi pengurus RT.', null, 403);
        }

        // Generate stateless API Bearer Token (Valid for 30 days)
        $token = 'ew_' . Str::random(60);
        Cache::put("api_token:{$token}", $user->id, now()->addDays(30));

        return $this->success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 86400, // in seconds
            'user'       => $user,
        ], 'Login berhasil.');
    }

    /**
     * Retrieve currently authenticated user profile and environment info.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $user->load(['tenant', 'house.vehicles', 'vehicles']);

        return $this->success($user, 'Profil pengguna aktif berhasil diambil.');
    }

    /**
     * Revoke active API Bearer Token.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token) {
            Cache::forget("api_token:{$token}");
        }

        return $this->success(null, 'Logout berhasil. Sesi API telah diakhiri.');
    }
}
