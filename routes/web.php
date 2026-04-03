<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('landing'))->name('landing');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::post('/account/update', [AuthController::class, 'updateAccount'])->name('account.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [DashboardController::class, 'search'])->name('search');

   Route::middleware(['role:admin,inventory'])->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/movement', [InventoryController::class, 'storeMovement'])->name('inventory.movement');
    Route::put('/inventory/{inventoryItem}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/{inventoryItem}/archive', [InventoryController::class, 'archive'])->name('inventory.archive');
    Route::post('/inventory/{inventoryItem}/restore', [InventoryController::class, 'restore'])->name('inventory.restore');
    Route::delete('/inventory/{inventoryItem}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
});

    Route::middleware(['role:admin,production'])->group(function () {
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/production', [ProductionController::class, 'store'])->name('production.store');
    Route::put('/production/{productionBatch}', [ProductionController::class, 'update'])->name('production.update');
    Route::post('/production/{productionBatch}/archive', [ProductionController::class, 'archive'])->name('production.archive');
    Route::post('/production/{productionBatch}/restore', [ProductionController::class, 'restore'])->name('production.restore');
    Route::delete('/production/{productionBatch}', [ProductionController::class, 'destroy'])->name('production.destroy');
});

   Route::middleware(['role:admin,production,manager'])->group(function () {
      Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    });

   Route::middleware(['role:admin,manager,inventory,production'])->group(function () {
      Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    });
    Route::middleware(['role:admin,inventory'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/preview', [OrderController::class, 'preview'])->name('orders.preview');
    Route::post('/orders/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system');
       Route::post('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password');
Route::post('/settings/restore', [SettingsController::class, 'restore'])->name('settings.restore');
Route::get('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    });
});