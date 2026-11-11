<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuthenticate
{
    /**
     * Handle an incoming API request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token) {
            $userId = Cache::get("api_token:{$token}");
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    Auth::setUser($user);
                }
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated. Token otentikasi tidak valid, kedaluwarsa, atau tidak disertakan.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}

