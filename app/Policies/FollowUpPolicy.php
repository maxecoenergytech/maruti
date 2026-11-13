<?php

namespace App\Policies;

use App\Models\FollowUp;
use App\Models\User;

class FollowUpPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() || $followUp->assigned_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() || $followUp->assigned_user_id === $user->id;
    }

    public function delete(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin() || $followUp->assigned_user_id === $user->id;
    }

    public function restore(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, FollowUp $followUp): bool
    {
        return $user->isAdmin();
    }
}
