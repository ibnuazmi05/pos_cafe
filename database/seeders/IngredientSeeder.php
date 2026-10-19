<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        // [nama, satuan, stok awal, stok minimum, harga beli per satuan]
        $rows = [
            ['Biji Kopi', 'gram', 5000, 1000, 250],
            ['Susu Cair', 'ml', 10000, 2000, 18],
            ['Gula Pasir', 'gram', 3000, 500, 16],
            ['Sirup Gula Aren', 'ml', 2000, 400, 40],
            ['Cokelat Bubuk', 'gram', 2000, 400, 90],
            ['Teh Celup', 'pcs', 200, 40, 600],
            ['Roti Tawar', 'lembar', 100, 20, 1500],
            ['Mentega', 'gram', 1000, 200, 60],
            ['Selai Cokelat', 'gram', 1000, 200, 55],
        ];

        foreach ($rows as [$name, $unit, $stock, $min, $cost]) {
            // forceCreate: kolom `stock` tidak masuk $fillable (hanya StockService yang boleh mengubah).
            Ingredient::withTrashed()->firstOrNew(['name' => $name])
                ->forceFill([
                    'unit' => $unit,
                    'stock' => $stock,
                    'min_stock' => $min,
                    'last_cost' => $cost,
                ])->save();
        }
    }
}
