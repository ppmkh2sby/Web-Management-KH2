<?php

use App\Enum\Role; // <-- penting: pakai namespace yang benar
use App\Http\Controllers\Auth\RegisteredSantriController;
use App\Http\Controllers\Auth\RegisteredWaliController;
use App\Http\Controllers\Auth\RegisteredStaffController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Santri\DashboardController as SantriDashboard;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

/*
| Landing (/)
*/
Route::get('/', function (): RedirectResponse {
    return auth()->check() ? to_route('dashboard') : to_route('login');
});

// ---------- Auth (guest) ----------
Route::middleware('guest')->group(function () {
    Route::get('/register/santri', [RegisteredSantriController::class, 'create'])->name('register.santri');
    Route::post('/register/santri', [RegisteredSantriController::class, 'store']);

    Route::get('/register/wali', [RegisteredWaliController::class, 'create'])->name('register.wali');
    Route::post('/register/wali', [RegisteredWaliController::class, 'store']);

    Route::get('/register/staff', [RegisteredStaffController::class, 'create'])->name('register.staff');
    Route::post('/register/staff', [RegisteredStaffController::class, 'store']);
});

// ---------- Authenticated (umum) ----------
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Wali
    Route::middleware('role:wali')->prefix('wali')->name('wali.')->group(function () {
        Route::get('/anak-saya', function () {
            $user = auth()->user();
            $santriList = $user->waliOf()->with('user')->get();
            return view('wali.anak', compact('santriList'));
        })->name('anak');
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
Route::middleware(['auth','role:santri'])
    ->prefix('santri')->name('santri.')
    ->group(function () {
        Route::get('/home',     [SantriDashboard::class, 'home'])->name('home');
        Route::get('/profile',  [SantriDashboard::class, 'profile'])->name('profile');
        Route::get('/setting',  [SantriDashboard::class, 'setting'])->name('setting');

        Route::prefix('data')->name('data.')->group(function () {
            Route::get('/',                 [SantriDashboard::class, 'dataIndex'])->name('index');
            Route::get('/presensi',         [SantriDashboard::class, 'presensi'])->name('presensi');
            Route::get('/progres-keilmuan', [SantriDashboard::class, 'progres'])->name('progres');
            Route::get('/log-keluar-masuk', [SantriDashboard::class, 'log'])->name('log');
        });

        // alias: /santri/dashboard -> /santri/home
        Route::get('/dashboard', fn () => redirect()->route('santri.home'))->name('dashboard');
    });

require __DIR__.'/auth.php';
