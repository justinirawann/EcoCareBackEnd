<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// EcoCare API Routes
Route::prefix('ecocare')->group(function () {
    // Laporan Sampah
    Route::get('/reports', function () {
        return response()->json(['message' => 'Get all waste reports']);
    });
    Route::post('/reports', function (Request $request) {
        return response()->json(['message' => 'Create waste report', 'data' => $request->all()]);
    });
    
    // Penjualan Sampah Daur Ulang
    Route::get('/recycling', function () {
        return response()->json(['message' => 'Get recycling items']);
    });
    Route::post('/recycling', function (Request $request) {
        return response()->json(['message' => 'Create recycling item', 'data' => $request->all()]);
    });
    
    // Jasa Pengangkutan
    Route::get('/pickup', function () {
        return response()->json(['message' => 'Get pickup services']);
    });
    Route::post('/pickup', function (Request $request) {
        return response()->json(['message' => 'Order pickup service', 'data' => $request->all()]);
    });
    
    // Edukasi
    Route::get('/education', function () {
        return response()->json(['message' => 'Get education content']);
    });
});