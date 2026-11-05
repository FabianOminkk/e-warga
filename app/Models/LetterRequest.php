<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'letter_type',
        'letter_number',
        'applicant_name',
        'nik',
        'house_block',
        'purpose',
        'supporting_data',
        'status',
        'rejection_reason',
        'qr_verification_code',
        'signed_at',
        'signed_by',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function getLetterTypeTitleAttribute(): string
    {
        return match($this->letter_type) {
            'pengantar_ktp' => 'Surat Pengantar Pembuatan KTP',
            'pengantar_kk' => 'Surat Pengantar Kartu Keluarga (KK)',
            'domisili' => 'Surat Keterangan Domisili Warga',
            'sktm' => 'Surat Keterangan Tidak Mampu (SKTM)',
            'izin_usaha' => 'Surat Pengantar Keterangan Usaha (SKU)',
            'pengantar_nikah' => 'Surat Pengantar Nikah (Model N1-N4)',
            'kematian' => 'Surat Keterangan Kematian',
            default => 'Surat Keterangan RT',
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'approved' => ['label' => 'Disetujui & Diterbitkan', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'],
            'rejected' => ['label' => 'Ditolak', 'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'],
            default => ['label' => 'Menunggu Persetujuan RT', 'color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'],
        };
    }
}
