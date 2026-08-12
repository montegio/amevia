<?php

namespace App\Domain\Family\Exceptions;

use RuntimeException;

class FamilyMemberAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('User already belongs to this family.');
    }
}
