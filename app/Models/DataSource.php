<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataSource extends Model
{
    protected $fillable = [
        'name', 'slug', 'organization', 'source_type', 'adapter_key', 'base_url',
        'frequency', 'status', 'credential_reference', 'contact', 'last_synced_at', 'notes',
    ];

    protected function casts(): array
    {
        return ['last_synced_at' => 'datetime'];
    }

    public function syncRuns(): HasMany
    {
        return $this->hasMany(SyncRun::class);
    }
}
