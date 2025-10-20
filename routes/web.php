<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\SantriDashboardController;
use App\Http\Controllers\WaliDashboardController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
| Struktur routing utama Web Management KH2.
| Sudah dilengkapi pengaturan role-based redirect dan dashboard.
|--------------------------------------------------------------------------
*/

// 🌍 HALAMAN UMUM
Route::get('/', [FrontController::class, 'index'])->name('home');
Route::get('/about', [FrontController::class, 'about'])->name('about');


// 🔐 AUTHENTICATION (WEB)
Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'showLoginForm')->name('login');
    Route::post('login', 'login')->name('login.submit');
    Route::post('logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('register', 'showRegisterForm')->name('register');
    Route::post('register', 'register')->name('register.submit');
    Route::get('verify-email/{token}', 'verifyEmail')->name('verify.email');
});

Route::controller(ForgotPasswordController::class)->group(function () {
    Route::get('forgot-password', 'showLinkRequestForm')->name('password.request');
    Route::post('forgot-password', 'sendResetLinkEmail')->name('password.email');
    Route::get('reset-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('reset-password', 'reset')->name('password.update');
});


// 🧑‍💻 ADMIN ROUTE
Route::prefix('admin')->group(function () {
    // Login & Forgot
    Route::get('/login', [AdminController::class, 'login'])->name('admin_login');
    Route::post('/login', [AdminController::class, 'login_submit'])->name('admin_login_submit');
    Route::get('/forget-password', [AdminController::class, 'forget_password'])->name('admin_forget_password');
    Route::post('/forget-password', [AdminController::class, 'forget_password_submit'])->name('admin_forget_password_submit');

    // Dashboard (middleware admin)
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin_dashboard');
        Route::get('/logout', [AdminController::class, 'logout'])->name('admin_logout');
    });
});

// Reset Password Admin
Route::controller(AdminPasswordController::class)->group(function () {
    Route::get('admin/forgot-password', 'showForgotForm')->name('admin_forget_password');
    Route::post('admin/forgot-password', 'sendResetLink')->name('admin_forget_password_submit');
    Route::get('admin/reset-password/{token}', 'showResetForm')->name('admin_reset_password');
    Route::post('admin/reset-password', 'resetPassword')->name('admin_reset_password_submit');
});


// 🏫 DASHBOARD ROLE-BASED (USER WEB)
Route::middleware(['auth'])->group(function () {
    // Santri
    Route::get('/santri/dashboard', [SantriDashboardController::class, 'index'])->name('santri.dashboard');

    // Wali Santri
    Route::get('/wali/dashboard', [WaliDashboardController::class, 'index'])->name('wali.dashboard');

    // Degur
    Route::get('/degur/dashboard', function () {
        return view('dashboard.degur');
    })->name('degur.dashboard');

    // Pengurus
    Route::get('/pengurus/dashboard', function () {
        return view('dashboard.pengurus');
    })->name('pengurus.dashboard');
});

