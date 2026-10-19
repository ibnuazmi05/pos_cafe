<?php

use App\Http\Controllers\Kasir\CheckoutController;
use App\Http\Controllers\Kasir\PosController;
use App\Http\Controllers\Kasir\ShiftController;
use App\Http\Controllers\Kasir\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        Route::get('/shift', [ShiftController::class, 'index'])->name('shift.index');
        Route::post('/shift/open', [ShiftController::class, 'open'])->name('shift.open');
        Route::post('/shift/close', [ShiftController::class, 'close'])->name('shift.close');

        // Hanya bisa diakses bila ada shift aktif
        Route::middleware('shift.active')->group(function () {
            Route::get('/pos', [PosController::class, 'index'])->name('pos');
            Route::post('/checkout', CheckoutController::class)->name('checkout');
        });

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
        Route::post('/transactions/{transaction}/void-request', [TransactionController::class, 'requestVoid'])->name('transactions.void-request');
    });
