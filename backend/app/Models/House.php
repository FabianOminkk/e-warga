<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'block_name',
        'house_number',
        'full_address',
        'occupancy_status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function familyHead(): HasOne
    {
        return $this->hasOne(User::class)->where('is_family_head', true);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class, 'host_house_id');
    }

    public function getBlockAndNumberAttribute(): string
    {
        return "{$this->block_name} No. {$this->house_number}";
    }
}

