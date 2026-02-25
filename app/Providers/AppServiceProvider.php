<?php

namespace App\Providers;

use App\Models\Kehadiran;
use App\Models\Presensi;
use App\Models\LogKeluarMasuk;
use App\Models\ProgressKeilmuan;
use App\Policies\KehadiranPolicy;
use App\Policies\PresensiPolicy;
use App\Policies\LogKeluarMasukPolicy;
use App\Policies\ProgressKeilmuanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Kehadiran::class, KehadiranPolicy::class);
        Gate::policy(ProgressKeilmuan::class, ProgressKeilmuanPolicy::class);
        Gate::policy(LogKeluarMasuk::class, LogKeluarMasukPolicy::class);
        Gate::policy(Presensi::class, PresensiPolicy::class);
    }
}
