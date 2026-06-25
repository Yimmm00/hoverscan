<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefectRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DefectClassController extends Controller
{
    /**
     * Read historical defect records based on the selected YOLO classification card.
     */
    public function getRecords(string $className, Request $request): JsonResponse
    {
        // Query records matching the requested YOLO class type string
        $query = DefectRecord::where('defect_class', $className)
            ->orderBy('created_at', 'desc');

        // Optional: Filter down to a specific bridge context if requested by the client UI
        if ($request->has('bridge_name') && !empty($request->bridge_name) && $request->bridge_name !== 'undefined') {
            $query->where('bridge_name', $request->bridge_name);
        }

        $records = $query->get()->map(function($rec) {
            // Strip any duplicate leading slashes if they exist
            $cleanPath = ltrim($rec->image_path, '/');

            return [
                'dataset_id' => $rec->dataset_id,
                'bridge_name' => $rec->bridge_name,
                'defect_class' => $rec->defect_class,
                'severity' => $rec->severity,
                'confidence_score' => $rec->confidence_score,
                // ⚡ POINT DIRECTLY TO YOUR RUNNING PYTHON APP MEDIA DIRECTORY (PORT 8001)
                'image_url' => 'http://127.0.0.1:8001/' . $cleanPath, 
                'date_logged' => $rec->created_at ? $rec->created_at->format('Y-m-d H:i') : 'Unknown',
                'temperature' => $rec->temperature ?? 31,
                'humidity' => $rec->humidity ?? 78
            ];
        });

        return response()->json([
            'status' => 'success',
            'count' => $records->count(),
            'data' => $records
        ]);
    }
}