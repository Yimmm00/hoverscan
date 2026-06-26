<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AssetHubController;
use App\Models\DefectRecord;
use Carbon\Carbon;


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

Route::get('/web-api/dashboard/filter', function (Illuminate\Http\Request $request) {
    $range = $request->query('range', 'all');
    $query = DefectRecord::query();

    // Apply chronological threshold rules based on selection
    if ($range === 'today') {
        $query->where('created_at', '>=', Carbon::today());
    } elseif ($range === '7days') {
        $query->where('created_at', '>=', Carbon::now()->subDays(7));
    } elseif ($range === 'month') {
        $query->where('created_at', '>=', Carbon::now()->startOfMonth());
    }

    // Fetch the recent logs matching the filter window
    $logs = (clone $query)->orderBy('created_at', 'desc')->take(10)->get();

    // Aggregate counts by classification for the chart
    $allRecords = $query->get();
    $chartData = [
        'Potholes' => $allRecords->where('defect_class', 'potholes')->count(),
        'Spalling' => $allRecords->where('defect_class', 'concrete spalling')->count(),
        'Cracks'   => $allRecords->where('defect_class', 'crack')->count(),
        'Mold'     => $allRecords->where('defect_class', 'mold')->count(),
        'Rust'     => $allRecords->where('defect_class', 'rust')->count(),
        'Staining' => $allRecords->where('defect_class', 'staining')->count(),
    ];

    return response()->json([
        'success' => true,
        'logs' => $logs,
        'chart' => $chartData
    ]);
});

Route::delete('/web-api/defects/delete-annotation', function (Request $request) {
    $validated = $request->validate([
        'bridge_name'  => 'required|string',
        'defect_class' => 'required|string',
        'image_path'   => 'required|string',
    ]);

    // Find the matching manual entry row (confidence_score is null) and remove it
    $record = DefectRecord::where('bridge_name', $validated['bridge_name'])
        ->where('defect_class', $validated['defect_class'])
        ->where('image_path', $validated['image_path'])
        ->whereNull('confidence_score')
        ->orderBy('id', 'desc') // Target the most recently added one if duplicates exist
        ->first();

    if ($record) {
        $record->delete();
        return response()->json(['success' => true, 'message' => 'Annotation dropped from database.']);
    }

    return response()->json(['success' => false, 'message' => 'Target vector ledger record not found.'], 404);
});