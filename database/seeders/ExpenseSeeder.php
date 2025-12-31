<?php

namespace Database\Seeders;

use App\Models\Category;
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
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $categories = Category::all();
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
                    'user_id' => $user->id,
                    'category_id' => $categories->random()->id,
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ]);
            }
        }
    }
}
