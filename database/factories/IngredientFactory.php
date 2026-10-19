<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ingredient> */
class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'unit' => 'gram',
            'stock' => 1000,
            'min_stock' => 100,
            'last_cost' => 100,
        ];
    }
}
