<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
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

        $categories = ['Groceries', 'Bills', 'Alcohol & Tobacco', 'Other'];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
            );
        }
    }
}
