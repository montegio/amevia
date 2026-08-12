<?php

namespace App\Domain\Family\Actions;

use App\Domain\Family\Enums\FamilyMemberStatus;
use App\Domain\Family\Models\FamilyMember;
use App\Domain\Family\Exceptions\FamilyInvitationNotPendingException;

final class AcceptFamilyInvitationAction
{
    public function execute(FamilyMember $membership): FamilyMember
    {
        if ($membership->status !== FamilyMemberStatus::PENDING) {
		throw new FamilyInvitationNotPendingException();
        }

        $membership->update([
            'status' => FamilyMemberStatus::ACCEPTED,
            'joined_at' => now(),
        ]);

        return $membership->refresh();
    }
}
