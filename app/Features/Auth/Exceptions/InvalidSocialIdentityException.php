<?php

namespace App\Features\Auth\Exceptions;

use RuntimeException;

final class InvalidSocialIdentityException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Social identity token could not be verified.');
    }
}
