<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
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

        $group = Group::firstOrCreate(['name' => 'Personal']);

        $group->users()->syncWithoutDetaching([
            $user->id => ['role' => 'owner'],
        ]);
    }
}
