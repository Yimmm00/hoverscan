<?php

use App\Http\Controllers\Api\AssetHubController;
use App\Http\Controllers\Api\AiAnalysisController;
use App\Http\Controllers\Api\DefectClassController;
use Illuminate\Support\Facades\Route;

// External API endpoints (Automatically prefixed with /api/)
Route::get('/bridges', [AssetHubController::class, 'getAllBridges']);
Route::post('/analyze', [AiAnalysisController::class, 'analyzeImage']);
Route::get('/defect-class-records/{className}', [DefectClassController::class, 'getRecords']);