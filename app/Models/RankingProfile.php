<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingProfile extends Model
{
    protected $fillable = ['business_category_id', 'name', 'weights', 'methodology_note', 'active'];

    protected function casts(): array
    {
        return ['weights' => 'array', 'active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }
}
