<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Santri\DashboardController as SantriDashboard;
use App\Http\Controllers\Santri\PresensiController as SantriPresensiController;
use App\Http\Controllers\Wali\MonitoringController as WaliMonitoring;
use App\Http\Controllers\Ketertiban\KehadiranController as KetertibanKehadiranController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Landing (/)
*/
Route::view('/landing', 'landing')->name('landing');
Route::redirect('/', '/landing');

// ---------- Authenticated (umum) ----------
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Wali
    Route::middleware('role:wali')->prefix('wali')->name('wali.')->group(function () {
        Route::get('/anak/{santriCode}', [WaliMonitoring::class, 'overview'])->name('anak.overview');
        Route::get('/anak/{santriCode}/presensi', [WaliMonitoring::class, 'presensi'])->name('anak.presensi');
        Route::get('/anak/{santriCode}/progres', [WaliMonitoring::class, 'progres'])->name('anak.progres');
        Route::get('/anak/{santriCode}/log', [WaliMonitoring::class, 'log'])->name('anak.log');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---------- Admin ----------
Route::middleware(['auth','role:admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    });

// ---------- Santri ----------
Route::middleware(['auth'])
    ->prefix('santri')->name('santri.')
    ->group(function () {
        // Beranda bisa diakses oleh semua role setelah login
        Route::get('/home', [SantriDashboard::class, 'home'])->name('home');
        // alias: /santri/dashboard -> /santri/home
        Route::get('/dashboard', fn () => redirect()->route('santri.home'))->name('dashboard');

        // Semua fitur santri dibuka untuk seluruh role (degur/pengurus/wali juga bisa akses)
        Route::get('/profile',  [SantriDashboard::class, 'profile'])->name('profile');
        Route::get('/setting',  [SantriDashboard::class, 'setting'])->name('setting');

        Route::prefix('data')->name('data.')->group(function () {
            Route::get('/presensi',         [SantriDashboard::class, 'presensi'])->name('presensi');
            Route::get('/progres-keilmuan', [SantriDashboard::class, 'progres'])->name('progres');
            Route::get('/log-keluar-masuk', [SantriDashboard::class, 'log'])->name('log');
        });

        Route::middleware('role:santri')->group(function () {
            Route::resource('presensi', SantriPresensiController::class)->names('presensi')->only(['index','show','store','update','destroy']);
        });
    });

require __DIR__.'/auth.php';
