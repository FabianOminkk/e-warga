<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitizenReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'house_id',
        'report_code',
        'type',
        'category',
        'title',
        'description',
        'location',
        'photo_path',
        'priority',
        'status',
        'is_anonymous',
        'handled_by_user_id',
        'official_response',
        'responded_at',
        'resolved_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'responded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        if (str_starts_with($this->photo_path, 'uploads/')) {
            return asset($this->photo_path);
        }

        return route('reports.photo.show', $this->id);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'aspirasi' => 'Aspirasi & Usulan',
            'kerusakan_fasilitas' => 'Kerusakan Fasilitas',
            'kebersihan' => 'Kebersihan & Sampah',
            'keamanan' => 'Laporan Keamanan',
            default => 'Pengaduan Warga',
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'in_progress' => [
                'label' => 'Sedang Diproses',
                'color' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            ],
            'resolved' => [
                'label' => 'Selesai Ditangani',
                'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            ],
            'rejected' => [
                'label' => 'Ditutup / Ditolak',
                'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-200 dark:border-rose-800',
            ],
            default => [
                'label' => 'Menunggu Tindak Lanjut',
                'color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            ],
        };
    }

    public function getPriorityBadgeAttribute(): array
    {
        return match ($this->priority) {
            'urgent' => [
                'label' => 'Mendesak',
                'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300',
            ],
            'high' => [
                'label' => 'Prioritas Tinggi',
                'color' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/70 dark:text-orange-300',
            ],
            'low' => [
                'label' => 'Rendah',
                'color' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            ],
            default => [
                'label' => 'Normal',
                'color' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/70 dark:text-cyan-300',
            ],
        };
    }
}
