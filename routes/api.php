<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\ExceptionController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API Routes for CargoTrace (UAS Web Programming 2)
|--------------------------------------------------------------------------
*/

// --- Public Endpoints ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/track/{trackingNumber}', [TrackingController::class, 'track']);

// Backward compatibility alias for /api/auth/*
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// --- Protected Endpoints (Requires Laravel Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/auth/me', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Shipments Resource (GET, POST, GET {id}, PUT/PATCH {id}, DELETE {id})
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::get('/shipments/{id}', [ShipmentController::class, 'show']);
    Route::match(['put', 'patch'], '/shipments/{id}', [ShipmentController::class, 'update']);
    Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy']);

    // Tracking Events for Shipment (GET timeline, POST append-only event)
    Route::get('/shipments/{id}/tracking', [TrackingController::class, 'index']);
    Route::post('/shipments/{id}/tracking', [TrackingController::class, 'store']);

    // Exceptions Resource
    Route::get('/shipments/{id}/exceptions', [ExceptionController::class, 'index']);
    Route::post('/shipments/{id}/exceptions', [ExceptionController::class, 'store']);
    Route::patch('/exceptions/{id}', [ExceptionController::class, 'update']);

    // Carriers Resource
    Route::get('/carriers', [CarrierController::class, 'index']);
    Route::post('/carriers', [CarrierController::class, 'store']);

    // Locations Resource
    Route::get('/locations', [LocationController::class, 'index']);
    Route::post('/locations', [LocationController::class, 'store']);

    // In-App Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});
