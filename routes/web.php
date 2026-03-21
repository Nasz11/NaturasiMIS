<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─── PUBLIC ─────────────────────────────────────────
Route::get('/', fn() => view('landing'))->name('landing');

// Auth
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── AUTHENTICATED ──────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Account settings (all roles)
    Route::post('/account/update', [AuthController::class, 'updateAccount'])->name('account.update');

    // DashboarD
 Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [DashboardController::class, 'search'])->name('search');
    // Inventory (admin + inventory staff)
    Route::middleware(['role:admin,inventory'])->group(function () {
        Route::get('/inventory',                     [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory',                    [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{inventoryItem}',     [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventoryItem}',  [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // Production (admin + production staff)
    Route::middleware(['role:admin,production'])->group(function () {
        Route::get('/production',                         [ProductionController::class, 'index'])->name('production.index');
        Route::post('/production',                        [ProductionController::class, 'store'])->name('production.store');
        Route::put('/production/{productionBatch}',       [ProductionController::class, 'update'])->name('production.update');
        Route::delete('/production/{productionBatch}',    [ProductionController::class, 'destroy'])->name('production.destroy');
    });

    // Batches (admin + production staff)
    Route::middleware(['role:admin,production'])->group(function () {
        Route::get('/batches',             [BatchController::class, 'index'])->name('batches.index');
        Route::post('/batches',            [BatchController::class, 'store'])->name('batches.store');
        Route::put('/batches/{batch}',     [BatchController::class, 'update'])->name('batches.update');
        Route::delete('/batches/{batch}',  [BatchController::class, 'destroy'])->name('batches.destroy');
    });

    // Reports (all roles can view)
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    // Users (admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users',           [UserController::class, 'index'])->name('users.index');
        Route::post('/users',          [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',    [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Settings (admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings',                  [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/system',          [SettingsController::class, 'updateSystem'])->name('settings.system');
        Route::post('/settings/password',        [SettingsController::class, 'changePassword'])->name('settings.password');
    });

    // Logs (admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    });
});
