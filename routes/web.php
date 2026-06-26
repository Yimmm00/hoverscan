<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AssetHubController;
use App\Models\DefectRecord;
use Illuminate\Support\Facades\Route;


// 1. Map the root URL to your interactive UI dashboard view
Route::get('/', [DashboardController::class, 'index']);

// 2. Internal Web AJAX: Fetch real-time gallery images filtered by classification name
Route::get('/web-api/defects/gallery/{class}', function ($class) {
    $records = DefectRecord::where('defect_class', $class)
        ->orderBy('created_at', 'desc')
        ->get(['id', 'bridge_name', 'severity', 'confidence_score', 'image_path', 'created_at']);

    return response()->json([
        'success' => true,
        'data' => $records
    ]);
});

// 3. Internal Web AJAX: Commit new bridge registrations directly from the web panel form
Route::post('/web-api/bridges/register', [AssetHubController::class, 'registerNewBridge']);

Route::post('/web-api/defects/save-annotation', function (Request $request) {
    $validated = $request->validate([
        'bridge_name'      => 'required|string',
        'defect_class'     => 'required|string',
        'severity'         => 'required|string',
        'image_path'       => 'nullable|string',
        'temperature'      => 'nullable|numeric',
        'humidity'         => 'nullable|numeric',
        'bbox_coordinates' => 'required|array', // ⚡ Added validation check
    ]);

    $datasetId = 'AST-' . strtoupper(substr(md5(uniqid()), 0, 10));

    $record = App\Models\DefectRecord::create([
        'dataset_id'       => $datasetId,
        'bridge_name'      => $validated['bridge_name'],
        'defect_class'     => $validated['defect_class'],
        'severity'         => $validated['severity'],
        'confidence_score' => null, 
        'image_path'       => $validated['image_path'],
        'temperature'      => $validated['temperature'] ?? 31,
        'humidity'         => $validated['humidity'] ?? 78,
        'bbox_coordinates' => $validated['bbox_coordinates'], // ⚡ Saved array coordinates directly
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Manual annotation successfully logged to permanent ledger indexes.',
        'data'    => $record
    ]);
});