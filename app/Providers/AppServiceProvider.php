<?php

namespace App\Providers;

use App\Events\TicketCreated;
use App\Listeners\SendTicketNotification;
// use Illuminate\Support\Facades\Event; // <--- PENTING: Tambahkan Facade Event
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
        // --- DAFTARKAN EVENT DI SINI ---

    }
}
