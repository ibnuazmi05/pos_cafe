<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\RecipeRequest;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $products = Product::query()
            ->with('category')
            ->withCount('ingredients')
            ->when($f['q'] ?? null, fn($q, $v) => $q->where(
                fn($w) => $w->where('name', 'like', Format::like($v))->orWhere('sku', 'like', Format::like($v))
            ))
            ->when($f['category_id'] ?? null, fn($q, $v) => $q->where('category_id', $v))
            ->when($f['status'] ?? null, fn($q, $v) => $q->where('is_active', $v === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'filters' => $f,
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['categories' => Category::active()->orderBy('name')->pluck('name', 'id')]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);

        ActivityLogger::log('product.created', $product, ['name' => $product->name, 'price' => $product->price]);

        return redirect()->route('admin.products.recipe.edit', $product)
            ->with('success', 'Menu ditambahkan. Lanjutkan dengan mengatur resep agar stok berkurang otomatis.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::active()->orWhere('id', $product->category_id)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Menu diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Soft delete: riwayat transaksi tetap utuh (nama & harga tersimpan sebagai snapshot).
        $product->delete();
        ActivityLogger::log('product.deleted', $product, ['name' => $product->name]);

        return back()->with('success', 'Menu dihapus.');
    }

    public function recipe(Product $product): View
    {
        $product->load('ingredients');

        $rows = old('ingredients') ?? $product->ingredients
            ->map(fn($i) => ['ingredient_id' => (string) $i->id, 'quantity' => (float) $i->pivot->quantity])
            ->values()
            ->all();

        $ingredients = Ingredient::orderBy('name')->get(['id', 'name', 'unit']);

        return view('admin.products.recipe', [
            'product' => $product,
            'rows' => $rows,
            'ingredients' => $ingredients,
            'ingredientMap' => $ingredients->mapWithKeys(fn($i) => [(string) $i->id => ['unit' => $i->unit]]),
        ]);
    }

    public function updateRecipe(RecipeRequest $request, Product $product): RedirectResponse
    {
        $sync = collect($request->validated('ingredients') ?? [])
            ->mapWithKeys(fn($row) => [(int) $row['ingredient_id'] => ['quantity' => $row['quantity']]])
            ->all();

        $product->ingredients()->sync($sync);

        ActivityLogger::log('product.recipe_updated', $product, ['name' => $product->name, 'ingredients' => count($sync)]);

        return redirect()->route('admin.products.index')->with('success', "Resep {$product->name} disimpan.");
    }
}
