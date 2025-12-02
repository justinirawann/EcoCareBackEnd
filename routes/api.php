<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return response()->json([
            'id'    => $request->user()->id,
            'name'  => $request->user()->name,
            'email' => $request->user()->email,
            'role'  => $request->user()->role,
        ]);
    });
    Route::middleware('role:user')->group(function () {
        Route::get('/user/dashboard', function () {
            return response()->json([
                'message' => 'Dashboard User'
            ]);
        });
    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json([
                'message' => 'Dashboard Admin'
            ]);
        });
    });
    Route::middleware('role:petugas')->group(function () {
        Route::get('/petugas/dashboard', function () {
            return response()->json([
                'message' => 'Dashboard Petugas'
            ]);
        });
    });

});
