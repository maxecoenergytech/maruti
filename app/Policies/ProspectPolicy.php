<?php

namespace App\Policies;

use App\Models\Prospect;
use App\Models\User;

class ProspectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Prospect $prospect): bool
    {
        return $user->isAdmin() || $prospect->assigned_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Prospect $prospect): bool
    {
        return $user->isAdmin() || $prospect->assigned_user_id === $user->id;
    }

    public function delete(User $user, Prospect $prospect): bool
    {
        return $user->isAdmin() || $prospect->assigned_user_id === $user->id;
    }

    public function restore(User $user, Prospect $prospect): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Prospect $prospect): bool
    {
        return $user->isAdmin();
    }
}
