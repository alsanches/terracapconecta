<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRun extends Model
{
    protected $fillable = [
        'data_source_id', 'user_id', 'mode', 'status', 'received_count', 'imported_count',
        'rejected_count', 'error_message', 'metadata', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'data_source_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
