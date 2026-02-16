<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\DoctorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('doctors')->group(function () {
    Route::get('/',           [DoctorController::class, 'index']);
    Route::post('/',          [DoctorController::class, 'store']);
    Route::get('/{doctor}',   [DoctorController::class, 'show']);
    Route::put('/{doctor}',   [DoctorController::class, 'update']);
    Route::patch('/{doctor}', [DoctorController::class, 'update']);
    Route::delete('/{doctor}',[DoctorController::class, 'destroy']);

    Route::get('/{doctor}/availability', AvailabilityController::class);
});

Route::post('/appointments', [AppointmentController::class, 'book']);
