<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ProfileController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // 🔍 Info user login
    Route::get('/me', function (Request $request) {
        return response()->json([
            'id'    => $request->user()->id,
            'name'  => $request->user()->name,
            'email' => $request->user()->email,
            'roles' => $request->user()->roles->pluck('slug'),
        ]);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Dashboard Admin']);
        });
        Route::get('/admin/reports', [ReportController::class, 'index']);
        Route::put('/admin/reports/{id}/verify', [ReportController::class, 'verify']);
        Route::post('/admin/create-petugas', [AuthController::class, 'createPetugas']);
    });


    Route::middleware('role:petugas')->group(function () {
        Route::get('/petugas/dashboard', function () {
            return response()->json(['message' => 'Dashboard Petugas']);
        });
    });


    Route::middleware('role:user')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard User']);
        });
        Route::post('/reports/create', [ReportController::class, 'create']);
        Route::get('/reports/my-reports', [ReportController::class, 'myReports']);
        Route::put('/reports/{id}/update', [ReportController::class, 'update']);
        Route::put('/profile/update', [ProfileController::class, 'update']);
    });

});
