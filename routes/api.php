<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountyController;
use Illuminate\Support\Facades\Route;

// --- Nyilvános végpontok ---
Route::post('/login', [AuthController::class, 'login']);

Route::get('/counties', [CountyController::class, 'index']);
Route::get('/counties/{county}', [CountyController::class, 'show']);

Route::get('/cities', [CityController::class, 'index']);
Route::get('/cities/{city}', [CityController::class, 'show']);

// --- Tokenes (Bearer) védelem alatt álló végpontok ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/counties', [CountyController::class, 'store']);
    Route::put('/counties/{county}', [CountyController::class, 'update']);
    Route::delete('/counties/{county}', [CountyController::class, 'destroy']);

    Route::post('/cities', [CityController::class, 'store']);
    Route::put('/cities/{city}', [CityController::class, 'update']);
    Route::delete('/cities/{city}', [CityController::class, 'destroy']);
});
