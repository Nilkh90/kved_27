<?php

use App\Http\Controllers\Api\V1\CodeController;
use App\Http\Controllers\Api\V1\MappingController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')
    ->prefix('v1')
    ->group(function () {
        Route::get('/search', [SearchController::class, 'index']);
        Route::get('/code/{id}', [CodeController::class, 'show']);
        Route::get('/mapping/{kvedId}', [MappingController::class, 'show']);
    });

