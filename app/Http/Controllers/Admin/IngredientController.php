<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientRequest;
use App\Models\Ingredient;
use App\Services\ActivityLogger;
use App\Services\StockService;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'low' => ['nullable', 'boolean'],
        ]);

        $ingredients = Ingredient::query()
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where('name', 'like', Format::like($v)))
            ->when($request->boolean('low'), fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.ingredients.index', ['ingredients' => $ingredients, 'filters' => $f]);
    }

    public function create(): View
    {
        return view('admin.ingredients.form');
    }

    public function store(IngredientRequest $request, StockService $stock): RedirectResponse
    {
        DB::transaction(function () use ($request, $stock) {
            $ingredient = Ingredient::create($request->safe()->only(['name', 'unit', 'min_stock', 'last_cost']));

            $initial = (float) $request->validated('initial_stock', 0);
            if ($initial > 0) {
                $stock->stockIn($ingredient, $initial, null, $request->user(), 'Stok awal');
            }

            ActivityLogger::log('ingredient.created', $ingredient, ['name' => $ingredient->name]);
        });

        return redirect()->route('admin.ingredients.index')->with('success', 'Bahan ditambahkan.');
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('admin.ingredients.form', ['ingredient' => $ingredient]);
    }

    public function update(IngredientRequest $request, Ingredient $ingredient): RedirectResponse
    {
        // Stok tidak diubah di sini; gunakan menu Stok (tercatat di ledger).
        $ingredient->update($request->safe()->only(['name', 'unit', 'min_stock', 'last_cost']));

        ActivityLogger::log('ingredient.updated', $ingredient, ['name' => $ingredient->name]);

        return redirect()->route('admin.ingredients.index')->with('success', 'Bahan diperbarui.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        if ($ingredient->products()->exists()) {
            return back()->with('error', 'Bahan masih dipakai pada resep menu. Hapus dari resep terlebih dahulu.');
        }

        $ingredient->delete();
        ActivityLogger::log('ingredient.deleted', $ingredient, ['name' => $ingredient->name]);

        return back()->with('success', 'Bahan dihapus.');
    }
}
