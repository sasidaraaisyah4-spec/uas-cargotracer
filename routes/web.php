<?php

use App\Http\Controllers\DashboardWebController;
use App\Http\Controllers\TrackingWebController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes for CargoTrace (UAS Web Programming 2)
|--------------------------------------------------------------------------
*/

// Public Landing Page (Bento Grid + Leaflet.js Tracking)
Route::get('/', [TrackingWebController::class, 'index'])->name('tracking.home');

// Web Authentication Routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [WebAuthController::class, 'register'])->name('register.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// 1-Click Quick Demo Login (for presentation & examiner testing)
Route::get('/demo-login/{role}', [WebAuthController::class, 'quickLogin'])->name('demo.login');

// Authenticated Dashboard Routes
Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardWebController::class, 'index'])->name('dashboard');

    // Shipments
    Route::get('/shipments', [DashboardWebController::class, 'shipments'])->name('dashboard.shipments');
    Route::get('/shipments/create', [DashboardWebController::class, 'createShipmentForm'])->name('dashboard.shipments.create');
    Route::post('/shipments', [DashboardWebController::class, 'storeShipment'])->name('dashboard.shipments.store');
    Route::get('/shipments/{id}', [DashboardWebController::class, 'showShipment'])->name('dashboard.shipments.show');

    // Tracking Events & Exceptions (Operator / Admin)
    Route::post('/shipments/{id}/tracking', [DashboardWebController::class, 'addTrackingEvent'])->name('dashboard.shipments.tracking');
    Route::post('/shipments/{id}/exceptions', [DashboardWebController::class, 'reportException'])->name('dashboard.shipments.exceptions');
    Route::post('/exceptions/{id}/resolve', [DashboardWebController::class, 'resolveException'])->name('dashboard.exceptions.resolve');

    // Notifications
    Route::get('/notifications', [DashboardWebController::class, 'notifications'])->name('dashboard.notifications');
    Route::post('/notifications/{id}/read', [DashboardWebController::class, 'markNotificationRead'])->name('dashboard.notifications.read');
});
