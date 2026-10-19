<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $ing = Ingredient::pluck('id', 'name');

        $menu = [
            'Kopi' => [
                ['KOP-001', 'Espresso', 15000, ['Biji Kopi' => 18]],
                ['KOP-002', 'Americano', 18000, ['Biji Kopi' => 18]],
                ['KOP-003', 'Cafe Latte', 25000, ['Biji Kopi' => 18, 'Susu Cair' => 150]],
                ['KOP-004', 'Kopi Susu Gula Aren', 22000, ['Biji Kopi' => 18, 'Susu Cair' => 120, 'Sirup Gula Aren' => 20]],
            ],
            'Non-Kopi' => [
                ['NKP-001', 'Cokelat Panas', 22000, ['Cokelat Bubuk' => 30, 'Susu Cair' => 150, 'Gula Pasir' => 10]],
                ['NKP-002', 'Teh Manis', 10000, ['Teh Celup' => 1, 'Gula Pasir' => 15]],
            ],
            'Makanan' => [
                ['MKN-001', 'Roti Bakar Cokelat', 18000, ['Roti Tawar' => 2, 'Mentega' => 20, 'Selai Cokelat' => 25]],
            ],
        ];

        foreach ($menu as $categoryName => $products) {
            $category = Category::firstOrCreate(['name' => $categoryName], ['is_active' => true]);

            foreach ($products as [$sku, $name, $price, $recipe]) {
                $product = Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'price' => $price,
                        'is_active' => true,
                    ]
                );

                $product->ingredients()->sync(
                    collect($recipe)->mapWithKeys(
                        fn ($qty, $ingredientName) => [$ing[$ingredientName] => ['quantity' => $qty]]
                    )->all()
                );
            }
        }
    }
}
