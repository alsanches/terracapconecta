<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionalIndicator extends Model
{
    protected $fillable = [
        'administrative_region_id', 'data_source_id', 'key', 'label', 'value',
        'unit', 'reference_year', 'is_demo',
    ];

    protected function casts(): array
    {
        return ['value' => 'decimal:4', 'is_demo' => 'boolean'];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(AdministrativeRegion::class, 'administrative_region_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'data_source_id');
    }
}
