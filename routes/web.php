<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Facilities\FacilitiesController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// --- 1. HALAMAN UTAMA (LANDING) ---
Route::get('/', function () {
    return view('landing');
});

// --- 2. GROUP FACILITY (FH) ---
// Kita buat grup prefix 'fh' di luar middleware auth global
Route::prefix('fh')->name('fh.')->group(function () {

    // A. PUBLIC ROUTE (Bisa diakses Guest/Tamu)
    // URL: /fh
    Route::get('/', [FacilitiesController::class, 'index'])->name('index');

    // B. PROTECTED ROUTES (Hanya User Login)
    Route::middleware('auth')->group(function () {

        // Dashboard Facility (Khusus Admin/User Login)
        // URL: /fh/dashboard
        Route::get('/dashboard', [FacilitiesController::class, 'dashboard'])->name('dashboard');

        // Export Excel
        // URL: /fh/export
        Route::get('/export', [FacilitiesController::class, 'export'])->name('export');

        // Simpan Data Baru
        // URL: /fh/store
        Route::post('/store', [FacilitiesController::class, 'store'])->name('store');

        // Update Status
        // URL: /fh/{id}/update-status
        Route::put('/{id}/update-status', [FacilitiesController::class, 'updateStatus'])->name('update-status');
    });
});


// --- 3. ROUTE LAINNYA (DASHBOARD UMUM & PROFILE) ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('landing');
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notification Routes
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});

require __DIR__ . '/auth.php';
