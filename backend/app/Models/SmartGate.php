<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartGate extends Model
{
    use HasFactory;

    protected $attributes = [
        'status' => 'offline',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'device_uid',
        'secret_token',
        'ip_address',
        'status',
        'last_ping_at',
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
    ];

    protected $appends = [
        'is_online',
    ];

    public function isOnline(int $ttlSeconds = 30): bool
    {
        if ($this->status === 'maintenance') {
            return false;
        }

        return $this->last_ping_at !== null
            && $this->last_ping_at->greaterThanOrEqualTo(now()->subSeconds($ttlSeconds))
            && $this->last_ping_at->lessThanOrEqualTo(now()->addSeconds(15));
    }

    public function getIsOnlineAttribute(): bool
    {
        return $this->isOnline();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GateLog::class);
    }
}

