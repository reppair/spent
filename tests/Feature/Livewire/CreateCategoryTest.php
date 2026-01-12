<?php

use App\Livewire\CreateCategory;
use App\Models\Category;
use App\Models\Group;
use App\Models\User;

use function Pest\Livewire\livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('mount', function () {
    it('sets categoryForm.group_id from passed groupId', function () {
        $group = Group::factory()->create();

        livewire(CreateCategory::class, ['groupId' => $group->id, 'groups' => Group::whereId($group->id)->get()])
            ->assertSet('categoryForm.group_id', $group->id);
    });
});

describe('validation', function () {
    it('requires name', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $group->id, 'groups' => Group::whereId($group->id)->get()])
            ->set('categoryForm.name', '')
            ->call('createCategory')
            ->assertHasErrors(['categoryForm.name' => 'required']);
    });

    it('requires name to be max 255 characters', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $group->id, 'groups' => Group::whereId($group->id)->get()])
            ->set('categoryForm.name', str_repeat('a', 256))
            ->call('createCategory')
            ->assertHasErrors(['categoryForm.name' => 'max']);
    });

    it('requires name to be unique per group', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);
        Category::factory()->for($group)->create(['name' => 'Existing Category']);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $group->id, 'groups' => Group::whereId($group->id)->get()])
            ->set('categoryForm.name', 'Existing Category')
            ->call('createCategory')
            ->assertHasErrors(['categoryForm.name' => 'unique']);
    });

    it('allows same name in different groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $user->groups()->attach($group2);
        Category::factory()->for($group1)->create(['name' => 'Shared Name']);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $group2->id, 'groups' => Group::whereId($group2->id)->get()])
            ->set('categoryForm.name', 'Shared Name')
            ->call('createCategory')
            ->assertHasNoErrors(['categoryForm.name']);
    });
});

describe('createCategory', function () {
    it('creates category with form data', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $group->id, 'groups' => Group::whereId($group->id)->get()])
            ->set('categoryForm.name', 'New Category')
            ->call('createCategory');

        $this->assertDatabaseHas('categories', [
            'group_id' => $group->id,
            'name' => 'New Category',
        ]);
    });

    it('resets name after save', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $group->id, 'groups' => Group::whereId($group->id)->get()])
            ->set('categoryForm.name', 'New Category')
            ->call('createCategory')
            ->assertSet('categoryForm.name', '');
    });

    it('dispatches category-created event with categoryId', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $group->id, 'groups' => Group::whereId($group->id)->get()])
            ->set('categoryForm.name', 'New Category')
            ->call('createCategory')
            ->assertDispatched('category-created', categoryId: Category::first()->id);
    });
});

describe('authorization', function () {
    it('prevents creating category for group user does not belong to', function () {
        $user = User::factory()->create();
        $userGroup = Group::factory()->create();
        $otherGroup = Group::factory()->create();
        $user->groups()->attach($userGroup);

        $this->actingAs($user);

        livewire(CreateCategory::class, ['groupId' => $userGroup->id, 'groups' => Group::whereId($userGroup->id)->get()])
            ->set('categoryForm.group_id', $otherGroup->id)
            ->set('categoryForm.name', 'Sneaky Category')
            ->call('createCategory')
            ->assertForbidden();
    });
});
