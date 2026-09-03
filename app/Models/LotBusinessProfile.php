<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotBusinessProfile extends Model
{
    protected $fillable = [
        'lot_id', 'business_category_id', 'target_audience_score', 'demand_density_score',
        'income_fit_score', 'mobility_access_score', 'opportunity_gap_score', 'reasons',
    ];

    protected function casts(): array
    {
        return ['reasons' => 'array'];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }
}
