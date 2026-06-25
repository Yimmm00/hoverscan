<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetHubController extends Controller
{
    /**
     * Fetch all registered infrastructure targets.
     */
    public function getAllBridges(): JsonResponse
    {
        $bridges = Bridge::all()->map(function($bridge) {
            return [
                'id' => $bridge->id,
                'name' => $bridge->name,
                'district' => $bridge->district,
                'location' => $bridge->location_coords,
                'last_inspection' => $bridge->last_inspection ? $bridge->last_inspection->format('Y-m-d') : 'Pending',
                'total_anomalies' => $bridge->total_anomalies
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $bridges
        ]);
    }

    /**
     * Insert a new bridge registry tracking record.
     */
    public function registerNewBridge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'district' => 'required|string',
            'location_coords' => 'required|string'
        ]);

        $bridge = \App\Models\Bridge::create([
            'name' => $validated['name'],
            'district' => strtoupper($validated['district']),
            'location_coords' => $validated['location_coords'],
            'total_anomalies' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Bridge '{$bridge->name}' registered successfully."
        ], 201);
    }
}