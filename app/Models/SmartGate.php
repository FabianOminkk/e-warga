<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartGate extends Model
{
    use HasFactory;

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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GateLog::class);
    }
}

