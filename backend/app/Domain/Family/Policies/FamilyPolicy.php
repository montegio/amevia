<?php

namespace App\Domain\Family\Policies;

use App\Domain\Family\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    public function view(User $user, Family $family): bool
    {
        return $family->members()
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->exists();
    }

    public function manageMembers(User $user, Family $family): bool
    {
        $membership = $family->members()
            ->where('user_id', $user->id)
            ->first();

        return $membership?->canManageMembers() ?? false;
    }
}
