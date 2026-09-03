<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LotResource;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LotController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'region' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:80'],
        ]);

        $lots = Lot::query()->published()->with(['region', 'noticeItems.notice', 'businessProfiles.category'])
            ->when($validated['region'] ?? null, fn ($query, $region) => $query->whereHas(
                'region', fn ($regionQuery) => $regionQuery->where('slug', $region)->orWhere('official_code', $region)
            ))
            ->when($validated['category'] ?? null, fn ($query, $category) => $query->whereHas(
                'businessProfiles.category', fn ($categoryQuery) => $categoryQuery->where('slug', $category)
            ))
            ->orderByDesc('is_featured')->orderBy('code')->get();

        return LotResource::collection($lots)->additional(['meta' => ['count' => $lots->count()]]);
    }

    public function show(Lot $lot): LotResource
    {
        abort_unless($lot->status === 'published' && $lot->published_at, 404);

        return new LotResource($lot->load(['region', 'noticeItems.notice', 'businessProfiles.category']));
    }
}
