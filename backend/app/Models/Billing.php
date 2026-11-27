<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'house_id',
        'billing_period',
        'amount',
        'description',
        'status',
        'due_date',
    ];

    protected $casts = [
        'billing_period' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid' => ['label' => 'Lunas', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300'],
            'pending_verification' => ['label' => 'Menunggu Verifikasi Bendahara', 'color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300'],
            'overdue' => ['label' => 'Jatuh Tempo', 'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300'],
            default => ['label' => 'Belum Bayar', 'color' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'],
        };
    }
}

