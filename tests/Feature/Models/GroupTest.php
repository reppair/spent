<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created with factory', function () {
    $group = Group::factory()->create();

    expect($group)->toBeInstanceOf(Group::class)
        ->and($group->id)->toBeInt()
        ->and($group->name)->toBeString();
});

it('has many users through pivot', function () {
    $group = Group::factory()->create();
    $users = User::factory()->count(3)->create();

    $group->users()->attach($users->pluck('id'));

    expect($group->users)->toHaveCount(3);
});

it('can attach users with role', function () {
    $group = Group::factory()->create();
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $group->users()->attach([
        $owner->id => ['role' => 'owner'],
        $member->id => ['role' => 'member'],
    ]);

    expect($group->users()->wherePivot('role', 'owner')->first()->id)->toBe($owner->id)
        ->and($group->users()->wherePivot('role', 'member')->first()->id)->toBe($member->id);
});

it('has many categories', function () {
    $group = Group::factory()->create();

    Category::factory()->count(3)->for($group)->create();

    expect($group->categories)->toHaveCount(3)
        ->and($group->categories->first())->toBeInstanceOf(Category::class);
});

it('has many expenses', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $category = Category::factory()->for($group)->create();

    Expense::factory()->count(3)->for($group)->for($user)->for($category)->create();

    expect($group->expenses)->toHaveCount(3)
        ->and($group->expenses->first())->toBeInstanceOf(Expense::class);
});

it('has fillable name', function () {
    $group = Group::create(['name' => 'Household']);

    expect($group->name)->toBe('Household');
});

it('has currency with default value', function () {
    $group = Group::factory()->create();

    expect($group->currency)->toBe(config('app.currency'));
});
