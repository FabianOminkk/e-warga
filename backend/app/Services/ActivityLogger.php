<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Catat aktivitas keamanan dan operasional ke tabel audit log.
     *
     * @param string $action Kode aksi (contoh: 'auth.login', 'ktp.view', 'warga.update')
     * @param string $description Ringkasan manusiawi aktivitas
     * @param string $severity Tingkat urgensi: 'info', 'warning', atau 'danger'
     * @param array $properties Metadata tambahan (otomatis disanitasi dari password/token)
     * @param User|null $user Pengguna yang melakukan aksi (opsional, default: Auth::user())
     * @param Model|null $subject Objek yang dimutasi (misal instance User, LetterRequest, Payment)
     * @return ActivityLog|null
     */
    public static function log(
        string $action,
        string $description,
        string $severity = 'info',
        array $properties = [],
        ?User $user = null,
        ?Model $subject = null
    ): ?ActivityLog {
        try {
            $user = $user ?? Auth::user();
            $request = request();

            $actorName = $user ? $user->name : ($properties['actor_name'] ?? 'Tamu / Sistem');
            $actorRole = $user ? $user->role : ($properties['actor_role'] ?? 'guest');
            $tenantId = $user?->tenant_id ?? session('active_tenant_id');

            // Sanitasi properties agar tidak ada password/token mentah yang bocor (CWE-532)
            $sanitizedProps = self::sanitizeProperties($properties);

            $subjectType = $subject ? get_class($subject) : null;
            $subjectId = $subject ? $subject->getKey() : null;

            return ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $user?->id,
                'actor_name' => mb_substr($actorName, 0, 150),
                'actor_role' => mb_substr($actorRole, 0, 50),
                'action' => mb_substr($action, 0, 60),
                'description' => mb_substr($description, 0, 255),
                'severity' => in_array($severity, ['info', 'warning', 'danger']) ? $severity : 'info',
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
                'properties' => !empty($sanitizedProps) ? $sanitizedProps : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Failsafe: logging tidak boleh merusak request utama jika ada gangguan DB
            Log::warning("Gagal menulis ActivityLog: " . $e->getMessage(), [
                'action' => $action,
                'exception' => $e,
            ]);
            return null;
        }
    }

    /**
     * Sanitasi metadata untuk mencegah kebocoran informasi kredensial ke log.
     */
    protected static function sanitizeProperties(array $properties): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'secret',
            'api_token',
            'pin',
            'rfid_secret',
            'deploy_secret',
        ];

        foreach ($properties as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $properties[$key] = '[REDACTED_SENSITIVE]';
            } elseif (is_array($value)) {
                $properties[$key] = self::sanitizeProperties($value);
            }
        }

        return $properties;
    }
}

