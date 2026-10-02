<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\RemittanceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
// Index should go straight to login (dashboard if already authenticated)
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'landing'])->name('dashboard');
    
    // Collection module — ALL_ACCESS, ADMINISTRATOR, CO_ADMIN, COLLECTION_STAFF
    Route::prefix('modules/collection')
        ->name('collection.')
        ->middleware('role:ALL_ACCESS,ADMINISTRATOR,CO_ADMIN,COLLECTION_STAFF')
        ->group(function () {
            Route::get('/', [CollectionController::class, 'index'])->name('index');
            Route::get('/transactions', [CollectionController::class, 'transactions'])->name('transactions');
            Route::get('/transactions/create', [CollectionController::class, 'create'])->name('create');
            Route::post('/transactions', [CollectionController::class, 'store'])->name('store');
            Route::get('/transactions/{transaction}/attachment', [CollectionController::class, 'attachment'])->name('attachment');
            Route::get('/reports', [CollectionController::class, 'reports'])->name('reports');
        });

    // Legacy alias so existing dashboard links to /modules/collection keep working.
    Route::get('/modules/collection-home', fn () => redirect()->route('collection.index'))->name('modules.collection');

    // Finance module — ALL_ACCESS, ADMINISTRATOR, CO_ADMIN, FINANCE_STAFF
    Route::get('/modules/finance', fn () => Inertia::render('Modules/Finance'))
        ->middleware('role:ALL_ACCESS,ADMINISTRATOR,CO_ADMIN,FINANCE_STAFF')
        ->name('modules.finance');

    // Remittance module (Finance function) — ALL_ACCESS, ADMINISTRATOR, CO_ADMIN, FINANCE_STAFF
    Route::prefix('modules/remittance')
        ->name('remittance.')
        ->middleware('role:ALL_ACCESS,ADMINISTRATOR,CO_ADMIN,FINANCE_STAFF')
        ->group(function () {
            Route::get('/', [RemittanceController::class, 'index'])->name('index');
            Route::post('/', [RemittanceController::class, 'store'])->name('store');
            Route::get('/{remittance}/download', [RemittanceController::class, 'download'])->name('download');
            Route::delete('/{remittance}', [RemittanceController::class, 'destroy'])->name('destroy');
        });

    // Disbursement module — ALL_ACCESS, ADMINISTRATOR, CO_ADMIN, DISBURSEMENT_OFFICER
    Route::prefix('modules/disbursement')
        ->name('disbursement.')
        ->middleware('role:ALL_ACCESS,ADMINISTRATOR,CO_ADMIN,DISBURSEMENT_OFFICER')
        ->group(function () {
            Route::get('/', [DisbursementController::class, 'index'])->name('index');
            Route::get('/records', [DisbursementController::class, 'records'])->name('records');
            Route::get('/records/create', [DisbursementController::class, 'create'])->name('create');
            Route::post('/records', [DisbursementController::class, 'store'])->name('store');
            Route::get('/records/{disbursement}/attachment', [DisbursementController::class, 'attachment'])->name('attachment');
            Route::get('/reports', [DisbursementController::class, 'reports'])->name('reports');
        });

    // Reports module — available to every authenticated role.
    Route::get('/modules/reports', fn () => Inertia::render('Modules/Reports'))
        ->middleware('role:ALL_ACCESS,ADMINISTRATOR,CO_ADMIN,COLLECTION_STAFF,FINANCE_STAFF,DISBURSEMENT_OFFICER,VIEWER')
        ->name('modules.reports');

    // Administrator module — ALL_ACCESS, ADMINISTRATOR only
    Route::prefix('modules/admin')
        ->name('admin.')
        ->middleware('role:ALL_ACCESS,ADMINISTRATOR')
        ->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('index');
            Route::post('/users', [AdminController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
            Route::put('/users/{user}/password', [AdminController::class, 'updatePassword'])->name('users.password');
            Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
        });
});


