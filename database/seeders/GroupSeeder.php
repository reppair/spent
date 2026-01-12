<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use App\Role;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $martin = User::firstOrCreate(
            ['email' => 'martin@blagoev.xyz'],
            [
                'name' => 'Martin Blagoev',
                'password' => bcrypt('password'),
                'created_at' => now()->subMonths(6), // we seed expenses 6 months back, so match the creation timestamp
            ]
        );

        $lora = User::firstOrCreate(
            ['email' => 'lora@example.com'],
            [
                'name' => 'Lora A',
                'password' => bcrypt('password'),
                'created_at' => now()->subMonths(6),
            ]
        );

        $group = Group::firstOrCreate(['name' => 'Personal']);

        $group->users()->syncWithoutDetaching([
            $martin->id => ['role' => Role::Admin],
        ]);

        $group = Group::firstOrCreate(['name' => 'Household']);

        $group->users()->syncWithoutDetaching([
            $martin->id => ['role' => Role::Admin],
            $lora->id => ['role' => Role::Member],
        ]);
    }
}
