<?php

use App\Models\Group;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('createCategory', function () {
    it('allows user who belongs to the group (Group model)', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        expect($user->can('createCategory', $group))->toBeTrue();
    });

    it('denies user who does not belong to the group (Group model)', function () {
        $user = User::factory()->create();
        $ownGroup = Group::factory()->create();
        $otherGroup = Group::factory()->create();
        $user->groups()->attach($ownGroup);

        expect($user->can('createCategory', $otherGroup))->toBeFalse();
    });

    it('allows user who belongs to the group (group_id int)', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $user->groups()->attach($group);

        expect($user->can('createCategory', [Group::class, $group->id]))->toBeTrue();
    });

    it('denies user who does not belong to the group (group_id int)', function () {
        $user = User::factory()->create();
        $ownGroup = Group::factory()->create();
        $otherGroup = Group::factory()->create();
        $user->groups()->attach($ownGroup);

        expect($user->can('createCategory', [Group::class, $otherGroup->id]))->toBeFalse();
    });
});
