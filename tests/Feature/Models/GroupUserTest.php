<?php

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('uses GroupUser pivot model for user groups relationship', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $user->groups()->attach($group, ['role' => 'owner']);

    $pivot = $user->groups->first()->pivot;

    expect($pivot)->toBeInstanceOf(GroupUser::class);
});

it('uses GroupUser pivot model for group users relationship', function () {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $group->users()->attach($user, ['role' => 'member']);

    $pivot = $group->users->first()->pivot;

    expect($pivot)->toBeInstanceOf(GroupUser::class);
});
