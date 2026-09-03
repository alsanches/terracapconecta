<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdministrativeRegion extends Model
{
    protected $fillable = [
        'data_source_id', 'official_code', 'slug', 'name', 'area_sq_km',
        'center_latitude', 'center_longitude', 'geometry_json', 'source_version', 'source_url',
    ];

    protected function casts(): array
    {
        return [
            'area_sq_km' => 'decimal:4',
            'center_latitude' => 'float',
            'center_longitude' => 'float',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'data_source_id');
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(RegionalIndicator::class);
    }
}
