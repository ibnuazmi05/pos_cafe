<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\PurchaseOrderFilterRequest;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Superadmin hanya melihat PO yang dibuat Admin (read-only). */
class PurchaseOrderController extends Controller
{
    public function index(PurchaseOrderFilterRequest $request): View
    {
        $filters = $request->validated();

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'creator'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['supplier_id'] ?? null, fn ($q, $v) => $q->where('supplier_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.purchase-orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'statuses' => PurchaseOrderStatus::options(),
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        Gate::authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'creator', 'receiver', 'items.ingredient']);

        return view('superadmin.purchase-orders.show', ['order' => $purchaseOrder]);
    }
}
