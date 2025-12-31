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

        $categories = ['Groceries', 'Bills', 'Alcohol & Tobacco', 'Other'];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['group_id' => $group->id, 'name' => $name],
            );
        }
    }
}
