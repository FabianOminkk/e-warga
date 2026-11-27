<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleTimeout
{
    /**
     * Akhiri sesi akun terautentikasi secara otomatis apabila tidak ada aktivitas melewati batas waktu konfigurasi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || $request->is('api/*', 'login', 'logout', 'quick-login/*', 'register', 'simulator*')) {
            return $next($request);
        }

        $timeoutMinutes = max(1, (int) config('session.idle_timeout', 15));
        $timeoutSeconds = $timeoutMinutes * 60;
        $lastActivity = $request->session()->get('last_activity_at');
        $now = now()->timestamp;

        if ($lastActivity !== null && ($now - (int) $lastActivity) > $timeoutSeconds) {
            $user = Auth::user();
            if ($user) {
                ActivityLogger::log(
                    'auth.idle_timeout',
                    "Sesi pengguna {$user->name} ({$user->role}) berakhir otomatis karena tidak ada aktivitas selama {$timeoutMinutes} menit.",
                    'warning',
                    ['idle_minutes' => $timeoutMinutes],
                    $user
                );
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Sesi Anda berakhir otomatis karena tidak ada aktivitas selama {$timeoutMinutes} menit. Silakan login kembali.",
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('warning', "Sesi Anda berakhir otomatis karena tidak ada aktivitas selama {$timeoutMinutes} menit. Silakan login kembali.");
        }

        $request->session()->put('last_activity_at', $now);

        return $next($request);
    }
}
