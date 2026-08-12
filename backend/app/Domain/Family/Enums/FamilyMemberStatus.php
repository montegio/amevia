<?php

namespace App\Domain\Family\Enums;

enum FamilyMemberStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case REMOVED = 'removed';
}
