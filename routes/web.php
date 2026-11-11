<?php

use App\Http\Controllers\TrackingWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [TrackingWebController::class, 'index'])->name('tracking.home');
