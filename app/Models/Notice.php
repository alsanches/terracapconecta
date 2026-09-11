<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'title', 'modality', 'procedure_type', 'process_number', 'opens_at',
        'closes_at', 'published_on', 'deposit_deadline', 'proposal_deadline', 'auction_at',
        'status', 'public_visible', 'description', 'regions_summary', 'document_path',
        'document_url', 'official_page_url', 'proposal_url', 'result_url',
        'source_checked_at', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'date',
            'closes_at' => 'date',
            'published_on' => 'date',
            'deposit_deadline' => 'date',
            'proposal_deadline' => 'date',
            'auction_at' => 'datetime',
            'source_checked_at' => 'datetime',
            'public_visible' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(NoticeItem::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('public_visible', true)->where('status', '!=', 'draft');
    }

    public function isCurrentlyOpen(): bool
    {
        if (! $this->public_visible || $this->status !== 'open') {
            return false;
        }

        if ($deadline = $this->proposal_deadline ?? $this->closes_at) {
            return $deadline->copy()->endOfDay()->isFuture();
        }

        return ! $this->auction_at || $this->auction_at->isFuture();
    }

    public function publicStatus(): string
    {
        return $this->status === 'open' && ! $this->isCurrentlyOpen() ? 'closed' : $this->status;
    }
}
