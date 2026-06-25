<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiAnalysisController extends Controller
{
    /**
     * Unified Multipart Media Handler for Image Inference Execution.
     */
    public function analyzeImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg',
            'temperature' => 'numeric',
            'humidity' => 'numeric',
            'bridge_name' => 'string'
        ]);

        $temp = $request->input('temperature', 31.0);
        $humidity = $request->input('humidity', 78.0);
        $bridgeName = $request->input('bridge_name', 'Batang Sadong Bridge');

        $file = $request->file('file');

        try {
            $response = Http::attach(
                'file', 
                file_get_contents($file->getRealPath()), 
                $file->getClientOriginalName()
            )->post("http://127.0.0.1:8001/analyze", [
                'temperature' => $temp,
                'humidity' => $humidity,
                'bridge_name' => $bridgeName
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'AI Backend Server responded with status code: ' . $response->status()
                ], 500);
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Could not communicate with Python AI Core Engine. Ensure main.py is running on Port 8001.'
            ], 500);
        }
    }
}