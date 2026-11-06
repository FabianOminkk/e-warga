<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'house_id',
        'name',
        'phone_number',
        'email',
        'password',
        'role',
        'is_family_head',
        'nik',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_family_head' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function gateLogs(): HasMany
    {
        return $this->hasMany(GateLog::class);
    }

    public function securitySchedules(): HasMany
    {
        return $this->hasMany(SecuritySchedule::class);
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function posyanduMembers(): HasMany
    {
        return $this->hasMany(PosyanduMember::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isKetuaRw(): bool
    {
        return $this->role === 'ketua_rw';
    }

    public function isKetuaRt(): bool
    {
        return $this->role === 'ketua_rt';
    }

    public function isBendahara(): bool
    {
        return $this->role === 'bendahara';
    }

    public function isSekretaris(): bool
    {
        return $this->role === 'sekretaris';
    }

    public function isSatpam(): bool
    {
        return $this->role === 'satpam';
    }

    public function isWarga(): bool
    {
        return $this->role === 'warga';
    }

    public function isPengurus(): bool
    {
        return in_array($this->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara']);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin Platform',
            'ketua_rw' => 'Ketua RW (Rukun Warga)',
            'ketua_rt' => 'Ketua RT (Rukun Tetangga)',
            'sekretaris' => 'Sekretaris RT/RW',
            'bendahara' => 'Bendahara RT/RW',
            'satpam' => 'Petugas Satpam',
            'warga' => $this->is_family_head ? 'Kepala Keluarga' : 'Warga Penghuni',
            default => 'Warga',
        };
    }
}
