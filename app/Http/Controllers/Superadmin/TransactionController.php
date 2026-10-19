<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\TransactionFilterRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(TransactionFilterRequest $request): View
    {
        $filters = $request->validated();

        $transactions = Transaction::query()
            ->with(['cashier', 'verifier'])
            ->filter($filters)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = Transaction::query()
            ->filter($filters)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        return view('superadmin.transactions.index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $filters,
            'cashiers' => User::where('role', Role::Kasir->value)->orderBy('name')->get(['id', 'name']),
            'methods' => PaymentMethod::options(),
            'verifications' => VerificationStatus::options(),
        ]);
    }

    public function show(Transaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        $transaction->load([
            'cashier',
            'verifier',
            'shift',
            'items',
        ]);

        return view('superadmin.transactions.show', ['transaction' => $transaction]);
    }
}
