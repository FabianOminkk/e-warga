<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'type',
        'category',
        'title',
        'amount',
        'transaction_date',
        'receipt_image',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'income' ? '+ ' : '- ';
        return $prefix . 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}

