<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created with factory', function () {
    $category = Category::factory()->create();

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->id)->toBeInt()
        ->and($category->name)->toBeString();
});

it('has many expenses', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();

    Expense::factory()->count(3)->for($category)->for($user)->create();

    expect($category->expenses)->toHaveCount(3)
        ->and($category->expenses->first())->toBeInstanceOf(Expense::class);
});

it('has fillable name', function () {
    $category = Category::create(['name' => 'Groceries']);

    expect($category->name)->toBe('Groceries');
});
