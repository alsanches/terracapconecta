<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecommendationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __invoke(Request $request, RecommendationEngine $engine): JsonResponse
    {
        $validated = $request->validate(['query' => ['required', 'string', 'min:2', 'max:120']]);

        return response()->json($engine->recommend($validated['query']));
    }
}
