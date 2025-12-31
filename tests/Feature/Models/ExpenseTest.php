<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created with factory', function () {
    $expense = Expense::factory()->create();

    expect($expense)->toBeInstanceOf(Expense::class)
        ->and($expense->id)->toBeInt()
        ->and($expense->amount)->toBeInt();
});

it('belongs to a group', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $category = Category::factory()->for($group)->create();
    $expense = Expense::factory()->for($group)->for($user)->for($category)->create();

    expect($expense->group)->toBeInstanceOf(Group::class)
        ->and($expense->group->id)->toBe($group->id);
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->create();

    expect($expense->user)->toBeInstanceOf(User::class)
        ->and($expense->user->id)->toBe($user->id);
});

it('belongs to a category', function () {
    $category = Category::factory()->create();
    $expense = Expense::factory()->for($category)->create();

    expect($expense->category)->toBeInstanceOf(Category::class)
        ->and($expense->category->id)->toBe($category->id);
});

it('stores amount in cents', function () {
    $expense = Expense::factory()->create(['amount' => 1234]);

    expect($expense->amount)->toBe(1234);
});

it('has nullable notes', function () {
    $expenseWithNotes = Expense::factory()->create(['notes' => 'Test note']);
    $expenseWithoutNotes = Expense::factory()->create(['notes' => null]);

    expect($expenseWithNotes->notes)->toBe('Test note')
        ->and($expenseWithoutNotes->notes)->toBeNull();
});

it('has fillable attributes', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $category = Category::factory()->for($group)->create();

    $expense = Expense::create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 5000,
        'notes' => 'Lunch',
    ]);

    expect($expense->amount)->toBe(5000)
        ->and($expense->notes)->toBe('Lunch')
        ->and($expense->group_id)->toBe($group->id);
});
