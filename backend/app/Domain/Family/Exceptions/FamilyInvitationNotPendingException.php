<?php

namespace App\Domain\Family\Exceptions;

use RuntimeException;

class FamilyInvitationNotPendingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Family invitation is no longer pending.');
    }
}
