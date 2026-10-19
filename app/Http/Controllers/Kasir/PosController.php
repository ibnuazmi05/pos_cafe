<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::active()
            ->whereHas('category', fn($q) => $q->where('is_active', true))
            ->with(['category', 'ingredients'])
            ->orderBy('name')
            ->get()
            ->map(function (Product $p) {
                // Sisa porsi dari bahan paling terbatas (null = menu tanpa resep, tak terbatas)
                $max = null;
                foreach ($p->ingredients as $ingredient) {
                    $need = (float) $ingredient->pivot->quantity;
                    if ($need <= 0) {
                        continue;
                    }
                    $can = (int) floor((float) $ingredient->stock / $need + 1e-9);
                    $max = $max === null ? $can : min($max, $can);
                }

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'category_id' => $p->category_id,
                    'category' => $p->category->name,
                    'max' => $max,
                ];
            })
            ->values();

        return view('kasir.pos', [
            'shift' => $request->attributes->get('shift'),
            'config' => [
                'products' => $products,
                'categories' => $products->unique('category_id')
                    ->map(fn($p) => ['id' => $p['category_id'], 'name' => $p['category']])
                    ->sortBy('name')->values(),
                'taxEnabled' => Setting::get('tax_enabled', '0') === '1',
                'taxRate' => (float) Setting::get('tax_rate', 0),
                'discount' => (float) Setting::get('discount', 0),
                'rounding' => 500,
                'csrf' => csrf_token(),
                'checkoutUrl' => route('kasir.checkout'),
            ],
        ]);
    }
}
