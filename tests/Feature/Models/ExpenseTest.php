<?php

use App\Currency;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created with factory', function () {
    $expense = Expense::factory()->create();

    expect($expense)->toBeInstanceOf(Expense::class)
        ->and($expense->id)->toBeInt()
        ->and($expense->amount)->toBeFloat();
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

it('stores amount in cents and retrieves as decimal', function () {
    $expense = Expense::factory()->create(['amount' => 12.34]);

    expect($expense->amount)->toBe(12.34)
        ->and((int) $expense->getRawOriginal('amount'))->toBe(1234);
});

it('converts decimal amount to cents when setting', function () {
    $expense = new Expense;

    $expense->amount = 25.50;

    expect($expense->getAttributes()['amount'])->toBe(2550.0);
});

it('converts cents to decimal when getting', function () {
    $expense = Expense::factory()->create(['amount' => 75.25]);

    expect($expense->amount)->toBe(75.25)
        ->and((int) $expense->getRawOriginal('amount'))->toBe(7525);
});

it('has nullable note', function () {
    $expenseWithNote = Expense::factory()->create(['note' => 'Test note']);
    $expenseWithoutNote = Expense::factory()->create(['note' => null]);

    expect($expenseWithNote->note)->toBe('Test note')
        ->and($expenseWithoutNote->note)->toBeNull();
});

it('has currency with default value', function () {
    $expense = Expense::factory()->create();

    expect($expense->currency)->toBe(config('app.currency'));
});

it('has fillable attributes', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $category = Category::factory()->for($group)->create();

    $expense = Expense::create([
        'group_id' => $group->id,
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 50.00,
        'note' => 'Lunch',
    ]);

    expect($expense->amount)->toEqual(50.00)
        ->and($expense->note)->toBe('Lunch')
        ->and($expense->group_id)->toBe($group->id);
});

it('returns formatted amount with currency sign', function () {
    $expense = Expense::factory()->create([
        'amount' => 12.34,
        'currency' => Currency::EUR,
    ]);

    expect($expense->formatted_amount)->toBe('€12.34');
});

it('formats amount with USD sign', function () {
    $expense = Expense::factory()->create([
        'amount' => 99.99,
        'currency' => Currency::USD,
    ]);

    expect($expense->formatted_amount)->toBe('$99.99');
});
