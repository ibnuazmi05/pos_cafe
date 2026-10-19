<?php

namespace Database\Factories;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Shift> */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->kasir(),
            'opened_at' => now(),
            'opening_cash' => 200000,
            'status' => ShiftStatus::Open,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $a) => [
            'status' => ShiftStatus::Closed,
            'closed_at' => now(),
            'expected_cash' => $a['opening_cash'],
            'closing_cash' => $a['opening_cash'],
            'difference' => 0,
        ]);
    }
}
