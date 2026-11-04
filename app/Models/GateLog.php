<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'smart_gate_id',
        'user_id',
        'visitor_id',
        'access_method',
        'direction',
        'status',
        'reason',
        'snapshot_image_url',
        'triggered_at',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function smartGate(): BelongsTo
    {
        return $this->belongsTo(SmartGate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
