<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosyanduRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'posyandu_member_id',
        'checkup_date',
        'weight_kg',
        'height_cm',
        'head_circumference_cm',
        'arm_circumference_cm',
        'blood_pressure',
        'blood_sugar',
        'cholesterol',
        'uric_acid',
        'gestational_age_weeks',
        'nutrition_status',
        'vitamins_or_vaccines',
        'examiner_name',
        'action_plan',
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'weight_kg' => 'float',
        'height_cm' => 'float',
        'head_circumference_cm' => 'float',
        'arm_circumference_cm' => 'float',
        'uric_acid' => 'float',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(PosyanduMember::class, 'posyandu_member_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->nutrition_status) {
            'gizi_baik', 'normal' => ['label' => 'Normal / Sehat', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'],
            'risiko_stunting' => ['label' => '⚠️ Waspada Stunting', 'color' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'],
            'gizi_kurang' => ['label' => 'Gizi Kurang', 'color' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300'],
            'gizi_lebih' => ['label' => 'Gizi Lebih', 'color' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300'],
            'hipertensi' => ['label' => 'Hipertensi', 'color' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'],
            'risiko_tinggi' => ['label' => 'Risiko Tinggi Kehamilan', 'color' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300'],
            default => ['label' => ucfirst($this->nutrition_status), 'color' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300'],
        };
    }
}

