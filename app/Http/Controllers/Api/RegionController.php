<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdministrativeRegionResource;
use App\Models\AdministrativeRegion;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    public function index(): JsonResponse
    {
        $regions = AdministrativeRegion::query()->with('source')
            ->withCount(['lots as published_lots_count' => fn ($query) => $query->published()])
            ->orderBy('name')->get();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => AdministrativeRegionResource::collection($regions)->resolve(),
            'meta' => ['count' => $regions->count(), 'crs' => 'EPSG:4326'],
        ]);
    }
}
