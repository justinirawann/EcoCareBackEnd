<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecyclingController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // 🔍 Info user login
    Route::get('/me', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'phone'   => $user->phone,
            'address' => $user->address,
            'image'   => $user->image,
            'roles'   => $user->roles->pluck('slug'),
        ]);
    });

    // Profile update untuk semua role
    Route::put('/profile/update', [ProfileController::class, 'update']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Dashboard Admin']);
        });
        Route::get('/admin/reports', [ReportController::class, 'index']);
        Route::put('/admin/reports/{id}/verify', [ReportController::class, 'verify']);
        Route::post('/admin/create-petugas', [AuthController::class, 'createPetugas']);
        Route::get('/admin/recycling', [RecyclingController::class, 'adminIndex']);
        Route::put('/admin/recycling/{id}/approve', [RecyclingController::class, 'approve']);
        Route::put('/admin/recycling/{id}/reject', [RecyclingController::class, 'reject']);
        Route::put('/admin/recycling/{id}/assign', [RecyclingController::class, 'assignPetugas']);
        Route::get('/admin/petugas', [AuthController::class, 'getPetugas']);
    });


    Route::middleware('role:petugas')->group(function () {
        Route::get('/petugas/dashboard', function () {
            return response()->json(['message' => 'Dashboard Petugas']);
        });
        Route::get('/petugas/recycling-tasks', [RecyclingController::class, 'petugasTasks']);
        Route::put('/petugas/recycling/{id}/complete', [RecyclingController::class, 'completeTask']);
    });


    Route::middleware('role:user')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Dashboard User']);
        });
        Route::post('/reports/create', [ReportController::class, 'create']);
        Route::get('/reports/my-reports', [ReportController::class, 'myReports']);
        Route::put('/reports/{id}/update', [ReportController::class, 'update']);
        Route::post('/recycling/create', [RecyclingController::class, 'create']);
        Route::get('/recycling/my-orders', [RecyclingController::class, 'myOrders']);
    });

});
