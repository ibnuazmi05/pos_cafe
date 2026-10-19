<?php

use App\Http\Controllers\Superadmin\ActivityLogController;
use App\Http\Controllers\Superadmin\DashboardController;
use App\Http\Controllers\Superadmin\EmployeeController;
use App\Http\Controllers\Superadmin\PurchaseOrderController;
use App\Http\Controllers\Superadmin\SettingsController;
use App\Http\Controllers\Superadmin\TransactionController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::resource('employees', EmployeeController::class)
            ->except(['show', 'destroy'])
            ->parameters(['employees' => 'user']);
        Route::patch('employees/{user}/toggle-active', [EmployeeController::class, 'toggleActive'])
            ->name('employees.toggle-active');
        // Route::patch('employees/{user}/reset-password', [EmployeeController::class, 'resetPassword'])
        //     ->name('employees.reset-password');

        Route::resource('transactions', TransactionController::class)->only(['index', 'show']);

        Route::resource('purchase-orders', PurchaseOrderController::class)
            ->only(['index', 'show'])
            ->parameters(['purchase-orders' => 'purchaseOrder']);

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
            Route::get('stock', [ReportController::class, 'stock'])->name('stock');
            Route::get('{type}/export/{format}', [ReportController::class, 'export'])
                ->whereIn('type', ['sales', 'purchases', 'stock'])
                ->whereIn('format', ['pdf', 'xlsx'])
                ->middleware('can:export-reports')
                ->name('export');
        });

        // Pengaturan cafe: PPN, batas diskon kasir, identitas struk
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
