<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'administrative_region_id', 'code', 'title', 'address', 'area_sqm', 'zoning',
        'destination', 'latitude', 'longitude', 'boundary_json', 'status', 'is_demo',
        'is_featured', 'search_enabled', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'area_sqm' => 'decimal:2',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_demo' => 'boolean',
            'is_featured' => 'boolean',
            'search_enabled' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(AdministrativeRegion::class, 'administrative_region_id');
    }

    public function noticeItems(): HasMany
    {
        return $this->hasMany(NoticeItem::class);
    }

    public function businessProfiles(): HasMany
    {
        return $this->hasMany(LotBusinessProfile::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }
}
