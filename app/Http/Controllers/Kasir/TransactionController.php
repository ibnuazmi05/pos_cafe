<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /** Riwayat milik sendiri pada shift yang sedang berjalan. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $shift = Shift::open()->where('user_id', $user->id)->latest('opened_at')->first();

        $transactions = Transaction::query()
            ->where('cashier_id', $user->id)
            ->where('shift_id', $shift?->id ?? 0)
            ->latest()
            ->paginate(25);

        $completed = Transaction::query()
            ->where('cashier_id', $user->id)
            ->where('shift_id', $shift?->id ?? 0);

        return view('kasir.transactions', [
            'shift' => $shift,
            'transactions' => $transactions,
            'completedCount' => (clone $completed)->count(),
            'completedTotal' => (float) (clone $completed)->sum('total'),
        ]);
    }

    public function receipt(Request $request, Transaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        $transaction->load(['items', 'cashier']);

        return view('kasir.receipt', [
            'transaction' => $transaction,
            'cafeName' => Setting::get('cafe_name', 'Cafe'),
            'cafeAddress' => Setting::get('cafe_address', ''),
            'autoprint' => $request->boolean('autoprint'),
        ]);
    }
}
