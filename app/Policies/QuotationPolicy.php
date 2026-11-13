<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Quotation $quotation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $quotation->created_by_user_id === $user->id ||
            $quotation->client?->assigned_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Quotation $quotation): bool
    {
        // Accepted quotations cannot be directly edited (must create revision)
        if ($quotation->isLocked()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $quotation->created_by_user_id === $user->id ||
            $quotation->client?->assigned_user_id === $user->id;
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        if ($quotation->isLocked()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $quotation->created_by_user_id === $user->id;
    }

    public function restore(User $user, Quotation $quotation): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Quotation $quotation): bool
    {
        return $user->isAdmin();
    }
}
