<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockInRequest;
use App\Http\Requests\Admin\StockOpnameRequest;
use App\Http\Requests\Admin\StockOutRequest;
use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Services\StockService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request): View
    {
        $f = $request->validate([
            'ingredient_id' => ['nullable', 'integer'],
            'type' => ['nullable', Rule::in(StockMovementType::values())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $movements = StockMovement::query()
            ->with(['ingredient', 'user'])
            ->when($f['ingredient_id'] ?? null, fn($q, $v) => $q->where('ingredient_id', $v))
            ->when($f['type'] ?? null, fn($q, $v) => $q->where('type', $v))
            ->when($f['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($f['date_to'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.stock.index', [
            'movements' => $movements,
            'filters' => $f,
            'ingredients' => Ingredient::orderBy('name')->get(['id', 'name', 'unit', 'stock', 'min_stock']),
            'types' => StockMovementType::options(),
        ]);
    }

    public function stockIn(StockInRequest $request): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($request->validated('ingredient_id'));

        $this->stock->stockIn(
            $ingredient,
            (float) $request->validated('quantity'),
            $request->filled('unit_cost') ? (float) $request->validated('unit_cost') : null,
            $request->user(),
            $request->validated('note'),
        );

        return back()->with('success', "Stok {$ingredient->name} bertambah.");
    }

    public function stockOut(StockOutRequest $request): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($request->validated('ingredient_id'));

        try {
            $this->stock->stockOut($ingredient, (float) $request->validated('quantity'), $request->user(), $request->validated('note'));
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Stok {$ingredient->name} berkurang.");
    }

    public function opname(StockOpnameRequest $request): RedirectResponse
    {
        $ingredient = Ingredient::findOrFail($request->validated('ingredient_id'));

        $movement = $this->stock->opname(
            $ingredient,
            (float) $request->validated('counted_stock'),
            $request->user(),
            $request->validated('note'),
        );

        $diff = (float) $movement->quantity;

        return back()->with('success', $diff == 0.0
            ? "Stok {$ingredient->name} sesuai, tidak ada selisih."
            : "Opname {$ingredient->name} dicatat (selisih " . ($diff > 0 ? '+' : '') . \App\Support\Format::qty($diff) . ' ' . $ingredient->unit . ').');
    }
}
