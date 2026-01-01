<?php

use App\Livewire\Dashboard;
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
