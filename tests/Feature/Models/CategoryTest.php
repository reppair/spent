<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\QueryException;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created with factory', function () {
    $category = Category::factory()->create();

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->id)->toBeInt()
        ->and($category->name)->toBeString();
});

it('belongs to a group', function () {
    $group = Group::factory()->create();
    $category = Category::factory()->for($group)->create();

    expect($category->group)->toBeInstanceOf(Group::class)
        ->and($category->group->id)->toBe($group->id);
});

it('has many expenses', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $category = Category::factory()->for($group)->create();

    Expense::factory()->count(3)->for($group)->for($category)->for($user)->create();

    expect($category->expenses)->toHaveCount(3)
        ->and($category->expenses->first())->toBeInstanceOf(Expense::class);
});

it('has fillable attributes', function () {
    $group = Group::factory()->create();
    $category = Category::create(['group_id' => $group->id, 'name' => 'Groceries']);

    expect($category->name)->toBe('Groceries')
        ->and($category->group_id)->toBe($group->id);
});

it('enforces unique name per group', function () {
    $group = Group::factory()->create();
    Category::factory()->for($group)->create(['name' => 'Groceries']);

    Category::factory()->for($group)->create(['name' => 'Groceries']);
})->throws(QueryException::class);

it('allows same name for different groups', function () {
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();

    $category1 = Category::factory()->for($group1)->create(['name' => 'Groceries']);
    $category2 = Category::factory()->for($group2)->create(['name' => 'Groceries']);

    expect($category1->name)->toBe('Groceries')
        ->and($category2->name)->toBe('Groceries')
        ->and($category1->group_id)->not->toBe($category2->group_id);
});
