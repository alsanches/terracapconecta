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
            'is_demo' => (bool) $this->is_demo,
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
                'payment_terms' => $noticeItem->payment_terms,
                'document_url' => $noticeItem->notice?->document_url,
            ] : null,
            'business_profiles' => $this->whenLoaded('businessProfiles', fn () => $this->businessProfiles->map(fn ($profile) => [
                'category' => $profile->category?->name, 'category_slug' => $profile->category?->slug,
            ])->values()),
            'disclaimer' => 'Dados fictícios para demonstração. Não constituem oferta oficial da Terracap.',
        ];
    }
}
