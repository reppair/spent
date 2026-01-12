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
        $martin = User::where('email', 'martin@blagoev.xyz')->first();
        $lora = User::where('email', 'lora@example.com')->first();

        if (! $martin) {
            return;
        }

        $monthsBack = 6;

        // Personal group - only Martin
        $personalGroup = $martin->groups()->where('name', 'Personal')->first();

        if ($personalGroup) {
            $this->seedExpensesForUser($martin, $personalGroup, 25, $monthsBack);
        }

        // Household group - Martin and Lora
        $householdGroup = $martin->groups()->where('name', 'Household')->first();

        if ($householdGroup) {
            $this->seedExpensesForUser($martin, $householdGroup, 25, $monthsBack);

            if ($lora) {
                $this->seedExpensesForUser($lora, $householdGroup, 25, $monthsBack);
            }
        }
    }

    private function seedExpensesForUser(User $user, $group, int $expensesPerMonth, int $monthsBack): void
    {
        $categories = $group->categories;

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
