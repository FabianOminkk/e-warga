<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'host_house_id',
        'invited_by_user_id',
        'guest_name',
        'guest_type',
        'vehicle_plate',
        'qr_token',
        'valid_from',
        'valid_until',
        'is_used',
        'checked_in_by_satpam_id',
        'checked_in_at',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'checked_in_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function hostHouse(): BelongsTo
    {
        return $this->belongsTo(House::class, 'host_house_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function satpam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by_satpam_id');
    }
}

