<?php

namespace App\Features\Auth\Exceptions;

use RuntimeException;

final class SocialProviderUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Social login is temporarily unavailable.')
    {
        parent::__construct($message);
    }
}
