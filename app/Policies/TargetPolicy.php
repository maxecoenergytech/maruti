<?php

namespace App\Policies;

use App\Models\Target;
use App\Models\User;

class TargetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Target $target): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $target->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Target $target): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Target $target): bool
    {
        return $user->isAdmin();
    }
}
