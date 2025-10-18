<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\WaliDashboardController;

// Halaman umum
Route::get('/', [FrontController::class, 'index'])->name('home');
Route::get('/about', [FrontController::class, 'about'])->name('about');

// ==== AUTH USER (WEB) ====
// Login
Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'showLoginForm')->name('login');
    Route::post('login', 'login')->name('login.submit');
    Route::post('logout', 'logout')->name('logout'); // POST untuk keamanan
});

// Register + verifikasi email
Route::controller(RegisterController::class)->group(function () {
    Route::get('register', 'showRegisterForm')->name('register');
    Route::post('register', 'register')->name('register.submit');
    Route::get('verify-email/{token}', 'verifyEmail')->name('verify.email');
});

// Lupa password (user web)
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::get('forgot-password', 'showLinkRequestForm')->name('password.request');
    Route::post('forgot-password', 'sendResetLinkEmail')->name('password.email');
    Route::get('reset-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('reset-password', 'reset')->name('password.update');
});

// ==== ADMIN (tetap seperti rencana) ====
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin_dashboard');
});
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('admin_login');
    Route::post('/login', [AdminController::class, 'login_submit'])->name('admin_login_submit');
    Route::get('/forget-password', [AdminController::class, 'forget_password'])->name('admin_forget_password');
    Route::post('/forget-password', [AdminController::class, 'forget_password_submit'])->name('admin_forget_password_submit');
    Route::get('/logout', [AdminController::class, 'logout'])->name('admin_logout');
});
Route::controller(AdminPasswordController::class)->group(function () {
    Route::get('admin/forgot-password', 'showForgotForm')->name('admin_forget_password');
    Route::post('admin/forgot-password', 'sendResetLink')->name('admin_forget_password_submit');
    Route::get('admin/reset-password/{token}', 'showResetForm')->name('admin_reset_password');
    Route::post('admin/reset-password', 'resetPassword')->name('admin_reset_password_submit');
});

// ==== WALI SANTRI DASHBOARD ====
Route::middleware(['auth'])->group(function () {
    Route::get('/wali/dashboard', [WaliDashboardController::class, 'index'])->name('wali.dashboard');
});