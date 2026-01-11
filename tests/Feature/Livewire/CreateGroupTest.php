<?php

use App\Livewire\CreateExpense;
use App\Livewire\CreateGroup;
use App\Models\Group;
use App\Models\User;
use App\Role;

use function Pest\Livewire\livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('mount', function () {
    it('renders the component', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->assertSuccessful();
    });

    it('defaults groupForm.name to empty string', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->assertSet('groupForm.name', '');
    });
});

describe('validation', function () {
    it('requires name', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->set('groupForm.name', '')
            ->call('createGroup')
            ->assertHasErrors(['groupForm.name' => 'required']);
    });

    it('requires name to be max 255 characters', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->set('groupForm.name', str_repeat('a', 256))
            ->call('createGroup')
            ->assertHasErrors(['groupForm.name' => 'max']);
    });
});

describe('createGroup', function () {
    it('creates group with form data', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->set('groupForm.name', 'New Group')
            ->call('createGroup');

        $this->assertDatabaseHas('groups', [
            'name' => 'New Group',
        ]);
    });

    it('attaches user as owner', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->set('groupForm.name', 'New Group')
            ->call('createGroup');

        $group = Group::where('name', 'New Group')->first();

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => Role::Owner->value,
        ]);
    });

    it('resets name after save', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->set('groupForm.name', 'New Group')
            ->call('createGroup')
            ->assertSet('groupForm.name', '');
    });

    it('dispatches group-created event with groupId', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->set('groupForm.name', 'New Group')
            ->call('createGroup')
            ->assertDispatched('group-created', groupId: Group::where('name', 'New Group')->first()->id);
    });
});

describe('CreateExpense integration', function () {
    it('selects new group when group-created event is received', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $newGroup = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($newGroup);

        $this->actingAs($user);

        livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups])
            ->dispatch('group-created', groupId: $newGroup->id)
            ->assertSet('expenseForm.group_id', $newGroup->id);
    });

    it('refreshes groups list when group-created event is received', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($group);

        $this->actingAs($user);

        $component = livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups]);

        $initialCount = $component->get('groups')->count();

        // Create a new group and attach user
        $newGroup = Group::factory()->hasCategories(1)->create();
        $user->groups()->attach($newGroup);

        // Dispatch the event
        $component->dispatch('group-created', groupId: $newGroup->id);

        // Groups should now include the new one
        expect($component->get('groups')->count())->toBe($initialCount + 1);
    });

    it('clears category_id when group-created event is received', function () {
        $user = User::factory()->create();
        $group = Group::factory()->hasCategories(2)->create();
        $user->groups()->attach($group);

        $newGroup = Group::factory()->create(); // New group has no categories
        $user->groups()->attach($newGroup);

        $this->actingAs($user);

        $component = livewire(CreateExpense::class, ['user' => $user, 'groups' => $user->groups]);

        // Initial category is selected
        expect($component->get('expenseForm.category_id'))->not->toBeNull();

        // Dispatch the event to select the new group
        $component->dispatch('group-created', groupId: $newGroup->id);

        // Category should be cleared since new group has no categories
        expect($component->get('expenseForm.category_id'))->toBeNull();
        expect($component->get('categories')->count())->toBe(0);
    });
});
