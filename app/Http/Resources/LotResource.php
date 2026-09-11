<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $noticeItem = $this->relationLoaded('noticeItems')
            ? ($this->noticeItems->firstWhere('status', 'open') ?? $this->noticeItems->first())
            : null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'address' => $this->address,
            'area_sqm' => (float) $this->area_sqm,
            'zoning' => $this->zoning,
            'destination' => $this->destination,
            'coordinates' => [(float) $this->longitude, (float) $this->latitude],
            'status' => $this->status,
            'offer_status' => $this->offer_status,
            'location_precision' => $this->location_precision,
            'is_demo' => (bool) $this->is_demo,
            'data_nature' => $this->is_demo ? 'demonstration' : 'official_reference',
            'source_url' => $this->source_url,
            'source_checked_at' => $this->source_checked_at?->toIso8601String(),
            'is_featured' => (bool) $this->is_featured,
            'search_enabled' => (bool) $this->search_enabled,
            'region' => $this->whenLoaded('region', fn () => [
                'id' => $this->region->id, 'code' => $this->region->official_code,
                'slug' => $this->region->slug, 'name' => $this->region->name,
            ]),
            'notice' => $noticeItem ? [
                'code' => $noticeItem->notice?->code, 'title' => $noticeItem->notice?->title,
                'item' => $noticeItem->item_number,
                'minimum_price' => $noticeItem->minimum_price ? (float) $noticeItem->minimum_price : null,
                'amount_type' => $noticeItem->amount_type,
                'appraisal_value' => $noticeItem->appraisal_value ? (float) $noticeItem->appraisal_value : null,
                'deposit_amount' => $noticeItem->deposit_amount ? (float) $noticeItem->deposit_amount : null,
                'payment_terms' => $noticeItem->payment_terms,
                'status' => $noticeItem->status,
                'outcome_notes' => $noticeItem->outcome_notes,
                'document_url' => $noticeItem->notice?->document_url,
                'official_page_url' => $noticeItem->notice?->official_page_url,
            ] : null,
            'business_profiles' => $this->whenLoaded('businessProfiles', fn () => $this->businessProfiles->map(fn ($profile) => [
                'category' => $profile->category?->name, 'category_slug' => $profile->category?->slug,
            ])->values()),
            'disclaimer' => $this->is_demo
                ? 'Dados fictícios para demonstração. Não constituem oferta oficial da Terracap.'
                : 'Referência pública real. Licitação fracassada e sem disponibilidade atual para proposta. Consulte a Terracap sobre eventual nova oferta.',
        ];
    }
}
