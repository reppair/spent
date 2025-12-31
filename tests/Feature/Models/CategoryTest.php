<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\QueryException;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created with factory', function () {
    $category = Category::factory()->create();

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->id)->toBeInt()
        ->and($category->name)->toBeString();
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    expect($category->user)->toBeInstanceOf(User::class)
        ->and($category->user->id)->toBe($user->id);
});

it('has many expenses', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    Expense::factory()->count(3)->for($category)->for($user)->create();

    expect($category->expenses)->toHaveCount(3)
        ->and($category->expenses->first())->toBeInstanceOf(Expense::class);
});

it('has fillable attributes', function () {
    $user = User::factory()->create();
    $category = Category::create(['user_id' => $user->id, 'name' => 'Groceries']);

    expect($category->name)->toBe('Groceries')
        ->and($category->user_id)->toBe($user->id);
});

it('enforces unique name per user', function () {
    $user = User::factory()->create();
    Category::factory()->for($user)->create(['name' => 'Groceries']);

    Category::factory()->for($user)->create(['name' => 'Groceries']);
})->throws(QueryException::class);

it('allows same name for different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category1 = Category::factory()->for($user1)->create(['name' => 'Groceries']);
    $category2 = Category::factory()->for($user2)->create(['name' => 'Groceries']);

    expect($category1->name)->toBe('Groceries')
        ->and($category2->name)->toBe('Groceries')
        ->and($category1->user_id)->not->toBe($category2->user_id);
});
