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
        
        $bridges = Bridge::with(['defectRecords' => function($query) {
            $query->orderBy('severity', 'desc'); 
        }])->withCount('defectRecords')->get();
        
        // ⚡ STREAM ONLY SEES THE TOP 5 ROWS NOW
        $recentLogs = DefectRecord::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // ⚡ NEW: FETCH ALL RECORDS TO ACCURATELY POPULATE CHART ON PAGE LOAD
        $allChartRecords = DefectRecord::all();

        return view('dashboard.index', [
            'total_bridges'   => $totalBridges,
            'total_anomalies' => $totalAnomalies,
            'avg_confidence'  => $avgConfidence,
            'bridges'         => $bridges,
            'recent_logs'     => $recentLogs,
            'all_chart_records' => $allChartRecords // Send the full dataset
        ]);
    }
}