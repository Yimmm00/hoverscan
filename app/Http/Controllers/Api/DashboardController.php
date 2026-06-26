<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bridge;
use App\Models\DefectRecord;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Compile database metrics and return the structured Master View layout.
     */
    public function index()
    {
        $totalBridges = Bridge::count();
        $totalAnomalies = DefectRecord::count();
        $avgConfRaw = DefectRecord::avg('confidence_score') ?? 0.0;
        $avgConfidence = round($avgConfRaw * 100, 1) . '%';
        
        // Inside DashboardController.php view builder logic
        $bridges = Bridge::with(['defectRecords' => function($query) {
            $query->orderBy('severity', 'desc'); // Order elements by severity matrix string rules
        }])->withCount('defectRecords')->get();
        
        $recentLogs = DefectRecord::orderBy('created_at', 'desc')
            ->take(4)
            ->get()
            ->map(function ($log) {
                return (object)[
                    'bridge_name' => $log->bridge_name,
                    'defect_class' => $log->defect_class,
                    'severity' => $log->severity
                ];
            });

        return view('dashboard.index', [
            'total_bridges' => $totalBridges,
            'total_anomalies' => $totalAnomalies,
            'avg_confidence' => $avgConfidence,
            'bridges' => $bridges,
            'recent_logs' => $recentLogs
        ]);
    }
}