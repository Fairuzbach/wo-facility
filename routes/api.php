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

// =================================================================
// 1. PUBLIC ROUTES (Bisa Diakses Tanpa Login / Guest)
// =================================================================

// --- AUTH: Login Token ---
Route::post('/login-token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Login gagal, cek email/password'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'token' => $token,
        'user' => $user
    ]);
});

// --- Helper Routes (Public) ---
// IMPORTANT: Route spesifik harus di atas route yang general/pattern matching
Route::get('/employee/{nik}', [WorkOrderFacilityController::class, 'getEmployeeByNik']);
Route::get('/machines/plant/{plantId}', [WorkOrderFacilityController::class, 'getMachinesByPlant']);

// --- Work Order Public Routes ---
Route::get('/facility-wo', [WorkOrderFacilityController::class, 'index']);
Route::get('/facility-wo/{id}', [WorkOrderFacilityController::class, 'show']);
Route::post('/facility-wo', [WorkOrderFacilityController::class, 'store']); // Guest dapat create


// =================================================================
// 2. PROTECTED ROUTES (Wajib Login / Punya Token)
// =================================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // User cek data diri sendiri
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Update Work Order (Admin/SPV only)
    Route::put('/facility-wo/{id}', [WorkOrderFacilityController::class, 'update']);
    Route::put('/facility-wo/{id}/update-status', [FacilitiesController::class, 'updateStatus']);

    // Export Data
    Route::get('/facility-wo/export/data', [WorkOrderFacilityController::class, 'export']);
});
