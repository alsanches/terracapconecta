<?php

use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\RegionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/regions', [RegionController::class, 'index']);
    Route::get('/lots', [LotController::class, 'index']);
    Route::get('/lots/{lot}', [LotController::class, 'show']);
    Route::get('/recommendations', RecommendationController::class);
});
