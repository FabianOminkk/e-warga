<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuritySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'schedule_type',
        'duty_date',
        'shift_name',
        'start_time',
        'end_time',
        'status',
        'replacement_user_id',
        'checked_in_at',
        'notes',
    ];

    protected $casts = [
        'duty_date' => 'date',
        'checked_in_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replacement_user_id');
    }

    public function getFormattedTimeAttribute(): string
    {
        $start = substr($this->start_time, 0, 5);
        $end = substr($this->end_time, 0, 5);
        return "{$start} - {$end} WIB";
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'scheduled' => ['label' => 'Terjadwal', 'class' => 'bg-blue-100 text-blue-800'],
            'present' => ['label' => 'Hadir / Siaga', 'class' => 'bg-emerald-100 text-emerald-800'],
            'swapped' => ['label' => 'Tukar Jadwal', 'class' => 'bg-amber-100 text-amber-800'],
            'absent' => ['label' => 'Berhalangan', 'class' => 'bg-red-100 text-red-800'],
            default => ['label' => 'Terjadwal', 'class' => 'bg-slate-100 text-slate-800'],
        };
    }
}
