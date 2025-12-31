<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Group;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $group = Group::where('name', 'Personal')->first();

        if (! $group) {
            return;
        }

        $categories = ['Alcohol & Tobacco', 'Lunch and Coffee', 'Vehicle Maintenance', 'Gas', 'Other'];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['group_id' => $group->id, 'name' => $name],
            );
        }


        $group = Group::where('name', 'Household')->first();

        if (! $group) {
            return;
        }

        $categories = ['Groceries', 'Bills', 'Subscriptions', 'Other'];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['group_id' => $group->id, 'name' => $name],
            );
        }
    }
}
