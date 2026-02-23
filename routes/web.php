<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Santri\DashboardController as SantriDashboard;
use App\Http\Controllers\Santri\ProgressKeilmuanController as SantriProgressKeilmuanController;
use App\Http\Controllers\Santri\LogKeluarMasukController as SantriLogKeluarMasukController;
use App\Http\Controllers\Santri\PresensiController as SantriPresensiController;
use App\Http\Controllers\Santri\KafarahController as SantriKafarahController;
use App\Http\Controllers\Wali\MonitoringController as WaliMonitoring;
use App\Http\Controllers\Ketertiban\KehadiranController as KetertibanKehadiranController;
use App\Http\Controllers\Ketertiban\KafarahController as KetertibanKafarahController;
use Illuminate\Support\Facades\Route;

/*
| Landing (/)
*/
Route::view('/landing', 'landing')->name('landing');
Route::redirect('/', '/landing');

// ---------- Authenticated (umum) ----------
Route::middleware(['auth'])->group(function () {
    // Halaman dashboard lama dinonaktifkan, arahkan ke landing.
    Route::redirect('/dashboard', '/landing')->name('dashboard');

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
        });

        Route::middleware('role:santri,pengurus,degur')->group(function () {
            Route::get('/data/progres-keilmuan', [SantriProgressKeilmuanController::class, 'index'])->name('data.progres');
            Route::post('/data/progres-keilmuan/sync', [SantriProgressKeilmuanController::class, 'sync'])->name('data.progres.sync');
            Route::resource('presensi', SantriPresensiController::class)->names('presensi')->only(['index','show','store','update','destroy','create']);
            Route::resource('kafarah', SantriKafarahController::class)->names('kafarah')->only(['index','show','store','update','destroy','create']);
        });

        Route::middleware('role:santri')->group(function () {
            Route::get('/data/log-keluar-masuk', [SantriLogKeluarMasukController::class, 'index'])->name('data.log');
            Route::post('/data/log-keluar-masuk', [SantriLogKeluarMasukController::class, 'store'])->name('data.log.store');
            Route::patch('/data/log-keluar-masuk/{logKeluarMasuk}', [SantriLogKeluarMasukController::class, 'update'])->name('data.log.update');
            Route::delete('/data/log-keluar-masuk/{logKeluarMasuk}', [SantriLogKeluarMasukController::class, 'destroy'])->name('data.log.destroy');
        });
    });

require __DIR__.'/auth.php';
