<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'user_id',
        'license_plate',
        'type',
        'brand_model',
        'rfid_card_uid',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

