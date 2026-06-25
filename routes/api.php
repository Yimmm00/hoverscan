<?php

use App\Http\Controllers\Api\AssetHubController;
use App\Http\Controllers\Api\AiAnalysisController;
use App\Http\Controllers\Api\DefectClassController;
use Illuminate\Support\Facades\Route;

// Stateless bridge registry endpoint entries
Route::post('/bridges/register', [AssetHubController::class, 'registerNewBridge']);
Route::get('/bridges', [AssetHubController::class, 'getAllBridges']);

// Core AI inference processing stream mappings
Route::post('/analyze', [AiAnalysisController::class, 'analyzeImage']);
Route::get('/defect-class-records/{className}', [DefectClassController::class, 'getRecords']);