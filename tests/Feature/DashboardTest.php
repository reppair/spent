<?php

use App\Livewire\Dashboard;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $user->groups()->attach($group);

    $this->actingAs($user)->get('/dashboard')->assertStatus(200);
});

test('selected groups are persisted to user settings', function () {
    $user = User::factory()->create();
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();
    $user->groups()->attach([$group1->id, $group2->id]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedGroups', [$group1->id, $group2->id]);

    $user->refresh();
    expect($user->settings['dashboard_selected_groups'])->toBe([$group1->id, $group2->id]);
});

test('selected groups are loaded from user settings on mount', function () {
    $user = User::factory()->create();
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();
    $user->groups()->attach([$group1->id, $group2->id]);
    $user->update(['settings' => ['dashboard_selected_groups' => [$group2->id]]]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSet('selectedGroups', [$group2->id]);
});

test('defaults to first group when no saved selection exists', function () {
    $user = User::factory()->create();
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();
    $user->groups()->attach([$group1->id, $group2->id]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSet('selectedGroups', [$group1->id]);
});

describe('selectedGroupsLabel', function () {
    test('returns "Select a group" when no groups are selected', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [])
            ->assertSet('selectedGroupsLabel', __('Select a group'));
    });

    test('returns single group name', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Family']);
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->assertSet('selectedGroupsLabel', 'Family');
    });

    test('returns comma-separated names for multiple groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Family']);
        $group2 = Group::factory()->create(['name' => 'Work']);
        $user->groups()->attach([$group1->id, $group2->id]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group1->id, $group2->id])
            ->assertSet('selectedGroupsLabel', 'Family, Work');
    });

    test('truncates long labels', function () {
        $user = User::factory()->create();
        $longName = str_repeat('A', 100);
        $group = Group::factory()->create(['name' => $longName]);
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->assertSet('selectedGroupsLabel', Str::limit($longName));
    });
});

describe('sorting', function () {
    test('defaults to created_at descending', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'desc');
    });

    test('toggles direction when sorting same column', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('sort', 'created_at')
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'asc')
            ->call('sort', 'created_at')
            ->assertSet('sortDirection', 'desc');
    });

    test('sets new column with ascending direction', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDirection', 'desc')
            ->call('sort', 'amount')
            ->assertSet('sortBy', 'amount')
            ->assertSet('sortDirection', 'asc');
    });
});

describe('expenses querying', function () {
    test('returns only expenses from selected groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        $expense1 = Expense::factory()->for($user)->for($group1)->create();
        $expense2 = Expense::factory()->for($user)->for($group2)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group1->id]);

        expect($component->get('expenses')->pluck('id')->all())->toBe([$expense1->id]);
    });

    test('excludes expenses from non-selected groups', function () {
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();
        $user->groups()->attach([$group1->id, $group2->id]);

        Expense::factory()->for($user)->for($group1)->create();
        Expense::factory()->for($user)->for($group2)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group1->id]);

        expect($component->get('expenses'))->toHaveCount(1);
    });

    test('eager loads category relationship', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        expect($component->get('expenses')->first()->relationLoaded('category'))->toBeTrue();
    });

    test('respects sort order', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        $expense1 = Expense::factory()->for($user)->for($group)->create(['amount' => 100]);
        $expense2 = Expense::factory()->for($user)->for($group)->create(['amount' => 300]);
        $expense3 = Expense::factory()->for($user)->for($group)->create(['amount' => 200]);

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id])
            ->set('sortBy', 'amount')
            ->set('sortDirection', 'asc');

        expect($component->get('expenses')->pluck('id')->all())
            ->toBe([$expense1->id, $expense3->id, $expense2->id]);
    });

    test('returns paginated results', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        Expense::factory()->for($user)->for($group)->count(20)->create();

        $component = Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->set('selectedGroups', [$group->id]);

        expect($component->get('expenses'))->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
        expect($component->get('expenses')->total())->toBe(20);
    });
});
