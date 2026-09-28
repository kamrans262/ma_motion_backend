<?php

namespace App\Features\Auth\Exceptions;

use RuntimeException;

final class AccountAlreadyExistsException extends RuntimeException
{
    public const CODE = 'account_already_exists';

    public function __construct()
    {
        parent::__construct('An account already exists with this email. Please log in instead.');
    }
}
