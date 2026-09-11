<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoticeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mainDeadline = $this->proposal_deadline ?? $this->closes_at ?? $this->auction_at;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'modality' => $this->modality,
            'procedure_type' => $this->procedure_type,
            'process_number' => $this->process_number,
            'description' => $this->description,
            'status' => $this->publicStatus(),
            'status_recorded' => $this->status,
            'is_current' => $this->isCurrentlyOpen(),
            'is_demo' => (bool) $this->is_demo,
            'data_nature' => $this->is_demo ? 'demonstration' : 'official_source',
            'published_on' => $this->published_on?->toDateString(),
            'opens_at' => $this->opens_at?->toDateString(),
            'closes_at' => $this->closes_at?->toDateString(),
            'deposit_deadline' => $this->deposit_deadline?->toDateString(),
            'proposal_deadline' => $this->proposal_deadline?->toDateString(),
            'auction_at' => $this->auction_at?->toIso8601String(),
            'main_deadline' => $mainDeadline?->toDateString(),
            'regions' => $this->regions_summary,
            'source_checked_at' => $this->source_checked_at?->toIso8601String(),
            'links' => [
                'official_page' => $this->official_page_url,
                'document' => $this->document_url,
                'proposal' => $this->proposal_url,
                'result' => $this->result_url,
            ],
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'item' => $item->item_number,
                'lot_id' => $item->lot_id,
                'lot_code' => $item->lot?->code,
                'amount_type' => $item->amount_type,
                'appraisal_value' => $item->appraisal_value !== null ? (float) $item->appraisal_value : null,
                'minimum_price' => $item->minimum_price !== null ? (float) $item->minimum_price : null,
                'deposit_amount' => $item->deposit_amount !== null ? (float) $item->deposit_amount : null,
                'status' => $item->status,
                'outcome_notes' => $item->outcome_notes,
            ])->values()),
        ];
    }
}
