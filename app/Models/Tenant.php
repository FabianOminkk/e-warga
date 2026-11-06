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
        'environment_type',
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

    public static function getActiveTenant(): ?self
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->role === 'super_admin' && session()->has('active_tenant_id')) {
            $tenant = self::find(session('active_tenant_id'));
            if ($tenant) {
                return $tenant;
            }
        }

        if ($user && $user->tenant_id) {
            $tenant = self::find($user->tenant_id);
            if ($tenant) {
                return $tenant;
            }
        }

        return self::first();
    }

    public function isCluster(): bool
    {
        return ($this->environment_type ?? 'cluster') === 'cluster';
    }

    public function isKampung(): bool
    {
        return ($this->environment_type ?? 'cluster') === 'kampung';
    }

    public function securitySchedules(): HasMany
    {
        return $this->hasMany(SecuritySchedule::class);
    }

    public function posyanduMembers(): HasMany
    {
        return $this->hasMany(PosyanduMember::class);
    }

    public function posyanduRecords(): HasMany
    {
        return $this->hasMany(PosyanduRecord::class);
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }
}

