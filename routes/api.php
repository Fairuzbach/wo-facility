<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Http\Controllers\Api\WorkOrderFacilityController;
use App\Http\Controllers\Facilities\FacilitiesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =================================================================
// 1. PUBLIC ROUTES (Bisa Diakses Tanpa Login / Guest)
// =================================================================

// GET List Work Order (Public Read)
Route::get('/facility-wo', [WorkOrderFacilityController::class, 'index']);

// GET Detail Work Order (Public Read)
Route::get('/facility-wo/{id}', [WorkOrderFacilityController::class, 'show']);

// POST Create Work Order (Public Create - Agar Tamu bisa buat tiket)
// --- PINDAHKAN DARI BAWAH KE SINI ---
Route::post('/facility-wo', [WorkOrderFacilityController::class, 'store']);

// Helper Cek NIK
Route::get('/employee/{nik}', function ($nik) {
    $employee = Employee::where('nik', $nik)->first();
    // Return null agar JS bisa handle jika data tidak ada, jangan error
    return response()->json($employee);
});
Route::get('/machines/plant/{plantId}', function ($plantId) {
    $machines = \App\Models\Engineering\Machine::where('plant_id', $plantId)
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    return response()->json($machines);
});

// =================================================================
// 2. PROTECTED ROUTES (Wajib Login / Punya Token)
// =================================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // User cek data diri sendiri
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Create dipindah ke atas (Public) ---

    // Update Data & Status (Hanya Admin/SPV yang login)
    Route::put('/facility-wo/{id}', [WorkOrderFacilityController::class, 'update']);
    Route::put('/facility-wo/{id}/update-status', [FacilitiesController::class, 'updateStatus']);

    // Export Data
    Route::get('/facility-wo/export/data', [WorkOrderFacilityController::class, 'export']);
});


// --- 3. AUTH ROUTES (Login) ---
Route::post('/login-token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Login gagal, cek email/password'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'token' => $token,
        'user' => $user
    ]);
});
