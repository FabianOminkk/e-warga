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
        'ktp_photo',
        'birth_date',
        'birth_place',
        'gender',
        'religion',
        'occupation',
        'address_ktp',
        'rfid_tag',
        'verification_status',
        'verified_by_user_id',
        'verified_at',
        'rejection_reason',
        'gate_access_enabled',
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
            'birth_date' => 'date',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'is_family_head' => 'boolean',
            'gate_access_enabled' => 'boolean',
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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function isVerifiedWarga(): bool
    {
        return $this->verification_status === 'approved' && $this->gate_access_enabled;
    }

    public function isPendingVerification(): bool
    {
        return $this->verification_status === 'pending_rw';
    }

    public function isUnverified(): bool
    {
        return in_array($this->verification_status, ['unverified', 'rejected']);
    }

    public function canAccessGate(): bool
    {
        // Staf keamanan dan super admin selalu bisa buka gerbang
        if (in_array($this->role, ['super_admin', 'ketua_rw', 'satpam'])) {
            return true;
        }

        // Warga harus berstatus approved dan akses gerbang aktif oleh RW
        return $this->verification_status === 'approved' && $this->gate_access_enabled;
    }

    public function getMaskedNikAttribute(): string
    {
        if (!$this->nik) {
            return 'Belum Diisi';
        }
        if (strlen($this->nik) < 8) {
            return $this->nik;
        }
        return substr($this->nik, 0, 6) . '******' . substr($this->nik, -4);
    }

    public function getVerificationBadgeAttribute(): array
    {
        return match ($this->verification_status) {
            'approved' => [
                'label' => 'Terverifikasi RW (Akses Aktif)',
                'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700',
                'icon' => '✅',
                'gate' => 'Aktif Terbuka',
            ],
            'pending_rw' => [
                'label' => 'Menunggu Persetujuan Ketua RW',
                'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border-amber-300 dark:border-amber-700',
                'icon' => '⏳',
                'gate' => 'Terkunci Sementara',
            ],
            'rejected' => [
                'label' => 'Ditolak / Butuh Revisi KTP',
                'badge' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border-rose-300 dark:border-rose-700',
                'icon' => '❌',
                'gate' => 'Terkunci',
            ],
            default => [
                'label' => 'Data KTP Belum Lengkap (Terkunci)',
                'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700',
                'icon' => '🔒',
                'gate' => 'Terkunci',
            ],
        };
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
