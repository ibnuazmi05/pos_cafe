<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\CheckoutRequest;
use App\Services\CheckoutService;
use DomainException;
use Illuminate\Http\JsonResponse;

/** Dipanggil via fetch() dari halaman POS; selalu membalas JSON. */
class CheckoutController extends Controller
{
    public function __invoke(CheckoutRequest $request, CheckoutService $checkout): JsonResponse
    {
        try {
            $transaction = $checkout->checkout(
                $request->user(),
                $request->attributes->get('shift'),
                $request->validated(),
            );
        } catch (DomainException $e) { // termasuk InsufficientStockException
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'invoice_no' => $transaction->invoice_no,
            'total' => (float) $transaction->total,
            'change' => (float) $transaction->change_amount,
            'receipt_url' => route('kasir.transactions.receipt', $transaction),
        ], 201);
    }
}
