<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Api\WorkOrderFacilityController;
use App\Http\Controllers\Facilities\FacilitiesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. PUBLIC ROUTES (Bisa Diakses Tanpa Login) ---
// User tamu bisa melihat daftar work order
Route::get('/facility-wo', [WorkOrderFacilityController::class, 'index']);
// User tamu bisa melihat detail work order
Route::get('/facility-wo/{id}', [WorkOrderFacilityController::class, 'show']);


// --- 2. PROTECTED ROUTES (Wajib Login / Punya Token) ---
Route::middleware(['auth:sanctum'])->group(function () {

    // User cek data diri sendiri
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Buat WO baru (Create)
    Route::post('/facility-wo', [WorkOrderFacilityController::class, 'store']);

    // Update status WO (Update)
    Route::put('/facility-wo/{id}', [WorkOrderFacilityController::class, 'update']);
    Route::put('/facility-wo/{id}/update-status', [FacilitiesController::class, 'updateStatus']);

    // Export Data (Saya tambahkan ini sesuai request Anda sebelumnya)
    // Pastikan nanti buat function 'export' di controller WorkOrderFacilityController
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

    // Buat token baru
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'token' => $token,
        'user' => $user
    ]);
});
