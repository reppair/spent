<?php

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

    it('attaches user as admin', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        livewire(CreateGroup::class)
            ->set('groupForm.name', 'New Group')
            ->call('createGroup');

        $group = Group::where('name', 'New Group')->first();

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => Role::Admin->value,
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
