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
        $user = User::firstOrCreate(
            ['email' => 'martin@blagoev.xyz'],
            [
                'name' => 'Martin Blagoev',
                'password' => bcrypt('password'),
            ]
        );

        $group = Group::firstOrCreate(['name' => 'Personal']);

        $group->users()->syncWithoutDetaching([
            $user->id => ['role' => Role::Owner],
        ]);

        $group = Group::firstOrCreate(['name' => 'Household']);

        $group->users()->syncWithoutDetaching([
            $user->id => ['role' => Role::Owner],
        ]);
    }
}
