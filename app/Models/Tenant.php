<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'rt_rw',
        'province',
        'city',
        'district',
        'postal_code',
        'subscription_plan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function smartGates(): HasMany
    {
        return $this->hasMany(SmartGate::class);
    }

    public function cashBooks(): HasMany
    {
        return $this->hasMany(CashBook::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }
}

