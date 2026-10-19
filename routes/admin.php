<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TransactionVerificationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Data master & stok
        Route::middleware('can:manage-master-data')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['show']);

            Route::resource('products', ProductController::class)->except(['show']);
            Route::get('products/{product}/recipe', [ProductController::class, 'recipe'])->name('products.recipe.edit');
            Route::put('products/{product}/recipe', [ProductController::class, 'updateRecipe'])->name('products.recipe.update');

            Route::resource('ingredients', IngredientController::class)->except(['show']);
            Route::resource('suppliers', SupplierController::class)->except(['show']);

            Route::get('stock', [StockController::class, 'index'])->name('stock.index');
            Route::post('stock/in', [StockController::class, 'stockIn'])->name('stock.in');
            Route::post('stock/out', [StockController::class, 'stockOut'])->name('stock.out');
            Route::post('stock/opname', [StockController::class, 'opname'])->name('stock.opname');
        });

        // Purchase order (otorisasi per aksi lewat PurchaseOrderPolicy)
        Route::resource('purchase-orders', PurchaseOrderController::class)
            ->parameters(['purchase-orders' => 'purchaseOrder']);
        Route::patch('purchase-orders/{purchaseOrder}/order', [PurchaseOrderController::class, 'order'])->name('purchase-orders.order');
        Route::patch('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::patch('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

        // Verifikasi transaksi kasir
        Route::get('verifications', [TransactionVerificationController::class, 'index'])->name('verifications.index');
        Route::get('verifications/{transaction}', [TransactionVerificationController::class, 'show'])->name('verifications.show');
        Route::patch('verifications/{transaction}', [TransactionVerificationController::class, 'update'])->name('verifications.update');

        // Pantau shift kasir & tutup paksa bila lupa ditutup
        Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::patch('shifts/{shift}/force-close', [ShiftController::class, 'forceClose'])->name('shifts.force-close');

        // Laporan (hanya lihat; tanpa data pegawai, laba kotor, dan export)
        Route::middleware('can:view-reports')->prefix('reports')->name('reports.')->group(function () {
            Route::get('sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
            Route::get('stock', [ReportController::class, 'stock'])->name('stock');
        });
    });
