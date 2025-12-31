<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        if (! $user) {
            return;
        }

        $group = $user->groups()->where('name', 'Personal')->first();

        if (! $group) {
            return;
        }

        $categories = $group->categories;
        $expensesPerMonth = 40;
        $monthsBack = 6;

        for ($month = 0; $month < $monthsBack; $month++) {
            $startOfMonth = Carbon::now()->subMonths($month)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonths($month)->endOfMonth();

            for ($i = 0; $i < $expensesPerMonth; $i++) {
                $randomDate = Carbon::createFromTimestamp(
                    fake()->numberBetween($startOfMonth->timestamp, $endOfMonth->timestamp)
                );

                Expense::factory()->create([
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                    'category_id' => $categories->random()->id,
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ]);
            }
        }
    }
}
