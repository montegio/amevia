<?php

namespace App\Domain\Family\DTOs;

use App\Domain\Family\Enums\FamilyRole;

final readonly class InviteFamilyMemberData
{
    public function __construct(
        public string $familyId,
        public string $userId,
        public string $invitedByUserId,
        public FamilyRole $role = FamilyRole::CONTRIBUTOR,
    ) {
    }
}
