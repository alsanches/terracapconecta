<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeItem extends Model
{
    protected $fillable = [
        'notice_id', 'lot_id', 'item_number', 'amount_type', 'minimum_price',
        'appraisal_value', 'deposit_amount', 'payment_terms', 'outcome_notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'minimum_price' => 'decimal:2',
            'appraisal_value' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }
}
