<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Facilities\FacilitiesController; // Pastikan nama Controller benar
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Models\Employee; // Tambahkan ini untuk route NIK

// --- 1. HALAMAN UTAMA (LANDING) ---
Route::get('/', function () {
    return view('landing');
});

// --- 2. API helper untuk NIK (Agar Javascript Check NIK jalan) ---
// Letakkan di sini agar tidak kena middleware auth
Route::get('/api/employee/{nik}', function ($nik) {
    return Employee::where('nik', $nik)->first();
});

// --- 3. GROUP FACILITY (FH) ---
Route::prefix('fh')->name('fh.')->group(function () {
    // === A. PUBLIC ROUTES (Bisa Diakses Guest / Tanpa Login) ===
    // 1. Halaman Utama Facility (List Tiket Public)
    Route::get('/', [FacilitiesController::class, 'index'])->name('index');

    // 2. Action Simpan Tiket (PENTING: Ini harus di luar middleware auth)
    Route::post('/store', [FacilitiesController::class, 'store'])->name('store');

    // === B. PROTECTED ROUTES (Wajib Login: Admin & SPV) ===
    Route::middleware('auth')->group(function () {

        // Dashboard Facility (Statistik & Monitoring)
        Route::get('/dashboard', [FacilitiesController::class, 'dashboard'])->name('dashboard');

        // Export Excel
        Route::get('/export', [FacilitiesController::class, 'export'])->name('export');

        // Update Status (Dilakukan oleh Admin Facility)
        Route::put('/{id}/update-status', [FacilitiesController::class, 'updateStatus'])->middleware('can:access-fh.admin')->name('update-status');

        // Approval Index
        Route::get('/approval', [FacilitiesController::class, 'approvalIndex'])->name('approval.index');

        // PERBAIKAN DI SINI:
        Route::post('/approve/{id}', [FacilitiesController::class, 'approve'])->name('approve');

        // Route Decline
        Route::post('/decline/{id}', [FacilitiesController::class, 'decline'])->name('decline');
    });
});


// --- 4. ROUTE LAINNYA (PROFILE & NOTIFIKASI) ---
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
// Route sementara untuk perbaiki data NULL
Route::get('/fix-data-divisi', function () {
    // 1. Ambil semua tiket yang divisinya masih NULL/Kosong
    $tickets = \App\Models\Facilities\WorkOrderFacilities::whereNull('requester_division')
        ->orWhere('requester_division', '')
        ->get();

    $count = 0;

    foreach ($tickets as $ticket) {
        // 2. Cari data karyawan berdasarkan NIK di tiket
        $emp = \App\Models\Employee::where('nik', $ticket->requester_nik)->first();

        if ($emp) {
            // 3. Update Divisi Tiket sesuai Departemen Karyawan
            $ticket->update([
                'requester_division' => $emp->department
            ]);
            $count++;
        }
    }

    return "Berhasil memperbaiki $count tiket. Silakan cek halaman approval lagi.";
});
require __DIR__ . '/auth.php';
