<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

// Map the root URL back to your main interactive UI dashboard view
Route::get('/', [DashboardController::class, 'index']);