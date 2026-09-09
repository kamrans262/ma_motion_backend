<?php

namespace App\Features\Auth\Exceptions;

use RuntimeException;

final class InactiveAccountException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This account is inactive.');
    }
}
