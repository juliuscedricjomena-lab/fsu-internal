<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'landing'])->name('dashboard');
    
    // Module routes
    Route::get('/modules/collection', function () {
        return Inertia::render('Modules/Collection');
    })->name('modules.collection');
    
    Route::get('/modules/finance', function () {
        return Inertia::render('Modules/Finance');
    })->name('modules.finance');
    
    Route::get('/modules/disbursement', function () {
        return Inertia::render('Modules/Disbursement');
    })->name('modules.disbursement');
    
    Route::get('/modules/reports', function () {
        return Inertia::render('Modules/Reports');
    })->name('modules.reports');
});


