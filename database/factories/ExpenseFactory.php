<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->numberBetween(100, 50000),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
