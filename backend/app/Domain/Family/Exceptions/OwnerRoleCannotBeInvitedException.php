<?php

namespace App\Domain\Family\Exceptions;

use RuntimeException;

class OwnerRoleCannotBeInvitedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Owner role cannot be assigned through invitation.'
        );
    }
}
