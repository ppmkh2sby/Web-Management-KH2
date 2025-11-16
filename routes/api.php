<?php

use App\Http\Controllers\Api\KehadiranController;
use App\Http\Controllers\Api\LogKeluarMasukController;
use App\Http\Controllers\Api\ProgressKeilmuanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->name('api.')->group(function () {
    Route::apiResource('kehadiran', KehadiranController::class);
    Route::apiResource('progress-keilmuan', ProgressKeilmuanController::class);
    Route::apiResource('log-keluar-masuk', LogKeluarMasukController::class);
});
