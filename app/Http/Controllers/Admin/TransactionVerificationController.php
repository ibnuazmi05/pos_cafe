<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifyTransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionVerificationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Transaction::class);

        $f = $request->validate([
            'verification_status' => ['nullable', Rule::in([...VerificationStatus::values(), 'all'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'cashier_id' => ['nullable', 'integer'],
        ]);

        $selected = $f['verification_status'] ?? VerificationStatus::Pending->value;

        $query = $f;
        if ($selected === 'all') {
            unset($query['verification_status']);
        } else {
            $query['verification_status'] = $selected;
        }

        $transactions = Transaction::query()
            ->with(['cashier', 'verifier'])
            ->filter($query)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.verifications.index', [
            'transactions' => $transactions,
            'filters' => $f,
            'selected' => $selected,
            'counts' => Transaction::query()->selectRaw('verification_status, COUNT(*) as c')->groupBy('verification_status')->pluck('c', 'verification_status'),
            'cashiers' => User::where('role', Role::Kasir->value)->orderBy('name')->pluck('name', 'id'),
            'statuses' => VerificationStatus::options(),
        ]);
    }

    public function show(Transaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        $transaction->load(['cashier', 'verifier', 'shift', 'items']);

        return view('admin.verifications.show', ['transaction' => $transaction]);
    }

    public function update(VerifyTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $status = VerificationStatus::from($request->validated('verification_status'));

        $transaction->update([
            'verification_status' => $status,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'note' => $request->validated('note'),
        ]);

        ActivityLogger::log('transaction.' . $status->value, $transaction, [
            'invoice' => $transaction->invoice_no,
            'note' => $request->validated('note'),
        ]);

        return redirect()->route('admin.verifications.show', $transaction)
            ->with('success', $status === VerificationStatus::Verified ? 'Transaksi diverifikasi.' : 'Transaksi ditandai untuk ditindaklanjuti.');
    }
}
