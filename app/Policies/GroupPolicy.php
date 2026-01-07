<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;

class GroupPolicy
{
    /**
     * Determine whether the user can create a category in the group.
     */
    public function createCategory(User $user, Group|int $group): bool
    {
        return is_int($group)
            ? GroupUser::whereGroupId($group)->whereUserId($user->id)->exists()
            : $user->groups->contains($group);
    }
}
