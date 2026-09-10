<?php

namespace App\Features\Auth\Contracts;

use App\Features\Auth\Data\SocialIdentity;

interface SocialIdentityVerifier
{
    public function verify(string $provider, string $idToken): SocialIdentity;
}
