<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdministrativeRegionResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'type' => 'Feature',
            'id' => $this->id,
            'geometry' => json_decode($this->geometry_json, true),
            'properties' => [
                'id' => $this->id,
                'code' => $this->official_code,
                'slug' => $this->slug,
                'name' => $this->name,
                'area_sq_km' => (float) $this->area_sq_km,
                'center' => [(float) $this->center_longitude, (float) $this->center_latitude],
                'lot_count' => (int) ($this->published_lots_count ?? 0),
                'source' => ['name' => $this->source?->name, 'version' => $this->source_version, 'url' => $this->source_url],
            ],
        ];
    }
}
