<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosyanduMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'family_card_number',
        'nik',
        'name',
        'gender',
        'birth_date',
        'category',
        'parent_name',
        'address',
        'phone',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(PosyanduRecord::class)->orderBy('checkup_date', 'desc');
    }

    public function latestRecord()
    {
        return $this->hasOne(PosyanduRecord::class)->latestOfMany('checkup_date');
    }

    public function getAgeTextAttribute(): string
    {
        if (!$this->birth_date) {
            return '-';
        }

        $diff = Carbon::now()->diff($this->birth_date);

        if ($this->category === 'balita') {
            $totalMonths = ($diff->y * 12) + $diff->m;
            return "{$totalMonths} Bulan";
        }

        return "{$diff->y} Tahun";
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'balita' => 'Balita (< 5 Thn)',
            'anak' => 'Anak-anak (5-12 Thn)',
            'ibu_hamil' => 'Ibu Hamil',
            'ibu' => 'Ibu / WUS',
            'lansia' => 'Lansia (> 60 Thn)',
            default => ucfirst($this->category),
        };
    }
}

