<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurchaseOrderRequest;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $service)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', PurchaseOrder::class);

        $f = $request->validate([
            'status' => ['nullable', Rule::in(PurchaseOrderStatus::values())],
            'supplier_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'creator'])
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['supplier_id'] ?? null, fn ($q, $v) => $q->where('supplier_id', $v))
            ->when($f['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($f['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.purchase-orders.index', [
            'orders' => $orders,
            'filters' => $f,
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'statuses' => PurchaseOrderStatus::options(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', PurchaseOrder::class);

        return view('admin.purchase-orders.form', $this->formData(null));
    }

    public function store(PurchaseOrderRequest $request): RedirectResponse
    {
        $po = $this->service->create($request->validated(), $request->user());

        return redirect()->route('admin.purchase-orders.show', $po)
            ->with('success', "PO {$po->po_no} disimpan sebagai draft.");
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        Gate::authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'creator', 'receiver', 'items.ingredient']);

        return view('admin.purchase-orders.show', ['order' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        Gate::authorize('update', $purchaseOrder);

        $purchaseOrder->load('items');

        return view('admin.purchase-orders.form', $this->formData($purchaseOrder));
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        return $this->run(fn () => $this->service->update($purchaseOrder, $request->validated(), $request->user()),
            route('admin.purchase-orders.show', $purchaseOrder), 'PO diperbarui.');
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        Gate::authorize('delete', $purchaseOrder);

        return $this->run(fn () => $this->service->delete($purchaseOrder, $request->user()),
            route('admin.purchase-orders.index'), 'PO draft dihapus.');
    }

    public function order(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        Gate::authorize('order', $purchaseOrder);

        return $this->run(fn () => $this->service->markOrdered($purchaseOrder, $request->user()),
            route('admin.purchase-orders.show', $purchaseOrder), 'PO ditandai sebagai dipesan.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        Gate::authorize('receive', $purchaseOrder);

        $date = $request->validate(['received_at' => ['nullable', 'date', 'before_or_equal:today']])['received_at'] ?? null;

        return $this->run(fn () => $this->service->receive($purchaseOrder, $request->user(), $date),
            route('admin.purchase-orders.show', $purchaseOrder), 'Barang diterima, stok bahan sudah bertambah.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        Gate::authorize('cancel', $purchaseOrder);

        return $this->run(fn () => $this->service->cancel($purchaseOrder, $request->user()),
            route('admin.purchase-orders.show', $purchaseOrder), 'PO dibatalkan.');
    }

    private function run(\Closure $action, string $redirectTo, string $successMessage): RedirectResponse
    {
        try {
            $action();
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect($redirectTo)->with('success', $successMessage);
    }

    private function formData(?PurchaseOrder $po): array
    {
        $ingredients = Ingredient::orderBy('name')->get(['id', 'name', 'unit', 'last_cost']);

        $rows = old('items') ?? ($po
            ? $po->items->map(fn ($i) => [
                'ingredient_id' => (string) $i->ingredient_id,
                'quantity' => (float) $i->quantity,
                'unit_price' => (float) $i->unit_price,
            ])->values()->all()
            : [['ingredient_id' => '', 'quantity' => '', 'unit_price' => '']]);

        return [
            'order' => $po,
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'ingredients' => $ingredients,
            'ingredientMap' => $ingredients->mapWithKeys(fn ($i) => [(string) $i->id => ['unit' => $i->unit, 'cost' => (float) $i->last_cost]]),
            'rows' => $rows,
        ];
    }
}
