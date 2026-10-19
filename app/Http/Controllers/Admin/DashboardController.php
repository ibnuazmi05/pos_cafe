<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $completed = Transaction::all();

        return view('admin.dashboard', [
            'lowStock' => Ingredient::lowStock()->orderBy('name')->get(),
            'pendingVerification' => (clone $completed)->where('verification_status', VerificationStatus::Pending->value)->count(),
            'flagged' => (clone $completed)->where('verification_status', VerificationStatus::Flagged->value)->count(),
            'orderedPo' => PurchaseOrder::where('status', PurchaseOrderStatus::Ordered->value)->count(),
            'draftPo' => PurchaseOrder::where('status', PurchaseOrderStatus::Draft->value)->count(),
        ]);
    }
}
