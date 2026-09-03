<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'title', 'modality', 'opens_at', 'closes_at', 'status',
        'description', 'document_path', 'document_url', 'is_demo',
    ];

    protected function casts(): array
    {
        return ['opens_at' => 'date', 'closes_at' => 'date', 'is_demo' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(NoticeItem::class);
    }
}
