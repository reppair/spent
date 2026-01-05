<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\Category;
use App\Models\User;
use App\Role;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a user with valid input', function () {
    $action = new CreateNewUser;

    $user = $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com');
});

it('creates a default group for new user', function () {
    $action = new CreateNewUser;

    $user = $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    expect($user->groups)->toHaveCount(1);
});

it('attaches user to group with owner role', function () {
    $action = new CreateNewUser;

    $user = $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $pivot = $user->groups->first()->pivot;

    expect($pivot->role)->toBe(Role::Owner->value);
});

it('creates default categories for the group', function () {
    $action = new CreateNewUser;

    $user = $action->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $group = $user->groups->first();

    expect(Category::whereGroupId($group->id)->count())->toBeGreaterThan(1);
});
