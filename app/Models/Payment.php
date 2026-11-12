<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'payer_user_id',
        'payment_method',
        'proof_image',
        'amount_paid',
        'verified_by_user_id',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}

