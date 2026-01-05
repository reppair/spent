<?php

namespace App\Actions\Fortify;

use App\Models\Category;
use App\Models\Group;
use App\Models\User;
use App\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        // create a default group
        $group = Group::firstOrCreate(['name' => 'Personal']);

        $group->users()->attach($user, ['role' => Role::Owner]);

        // and categories
        collect(['Food and Drinks', 'Vehicle Maintenance', 'Gas', 'Other'])
            ->each(fn ($category) => Category::create(['name' => $category, 'group_id' => $group->id]));

        return $user;
    }
}
