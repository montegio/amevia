<?php

namespace App\Domain\Family\Actions;

use App\Domain\Family\DTOs\InviteFamilyMemberData;
use App\Domain\Family\Enums\FamilyMemberStatus;
use App\Domain\Family\Enums\FamilyRole;
use App\Domain\Family\Models\FamilyMember;
use App\Domain\Family\Models\Family;
use App\Domain\Family\Exceptions\FamilyMemberAlreadyExistsException;
use App\Domain\Family\Exceptions\OwnerRoleCannotBeInvitedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

final class InviteFamilyMemberAction
{

public function execute(InviteFamilyMemberData $data): FamilyMember
{
    $family = Family::query()->findOrFail($data->familyId);

    Gate::forUser(
        \App\Models\User::query()->findOrFail($data->invitedByUserId)
    )->authorize('manageMembers', $family);

    if ($data->role === FamilyRole::OWNER) {
	throw new OwnerRoleCannotBeInvitedException();
    }

    $alreadyExists = FamilyMember::query()
        ->where('family_id', $data->familyId)
        ->where('user_id', $data->userId)
        ->exists();

    if ($alreadyExists) {
	throw new FamilyMemberAlreadyExistsException();
    }

    return FamilyMember::create([
        'family_id' => $data->familyId,
        'user_id' => $data->userId,
        'role' => $data->role,
        'status' => FamilyMemberStatus::PENDING,
        'invited_by_user_id' => $data->invitedByUserId,
        'invited_at' => now(),
    ]);
}

}
