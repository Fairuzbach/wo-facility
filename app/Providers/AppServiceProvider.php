<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <--- WAJIB DI IMPORT
use App\Models\User;

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
        // === DEFINISI GATE DISINI (KHUSUS LARAVEL 11) ===

        Gate::define('access-fh.admin', function ($user) {

            // Cek 1: Role spesifik
            if ($user->role === 'fh.admin') {
                return true;
            }

            // Cek 2: Role admin biasa + Divisi Facility
            if ($user->role === 'admin' && ($user->divisi === 'Facility' || $user->divisi === 'FH')) {
                return true;
            }

            return false;
        });
    }
}
