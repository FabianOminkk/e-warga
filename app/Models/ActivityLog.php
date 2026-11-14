<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false; // We only use created_at

    protected $fillable = [
        'tenant_id',
        'user_id',
        'actor_name',
        'actor_role',
        'action',
        'description',
        'severity',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getSeverityBadgeAttribute(): array
    {
        return match ($this->severity) {
            'danger' => [
                'bg' => 'bg-rose-50 dark:bg-rose-950/60',
                'text' => 'text-rose-700 dark:text-rose-300',
                'border' => 'border-rose-200 dark:border-rose-800',
                'dot' => 'bg-rose-500',
                'label' => 'DANGER / CRITICAL',
            ],
            'warning' => [
                'bg' => 'bg-amber-50 dark:bg-amber-950/60',
                'text' => 'text-amber-700 dark:text-amber-300',
                'border' => 'border-amber-200 dark:border-amber-800',
                'dot' => 'bg-amber-500',
                'label' => 'WARNING / AUDIT',
            ],
            default => [
                'bg' => 'bg-emerald-50 dark:bg-emerald-950/60',
                'text' => 'text-emerald-700 dark:text-emerald-300',
                'border' => 'border-emerald-200 dark:border-emerald-800',
                'dot' => 'bg-emerald-500',
                'label' => 'INFO / REGULER',
            ],
        };
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('description', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        });

        $query->when($filters['severity'] ?? null, function ($q, $severity) {
            if (in_array($severity, ['info', 'warning', 'danger'])) {
                $q->where('severity', $severity);
            }
        });

        $query->when($filters['tenant_id'] ?? null, function ($q, $tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        $query->when($filters['action'] ?? null, function ($q, $action) {
            $q->where('action', $action);
        });

        return $query;
    }
}
