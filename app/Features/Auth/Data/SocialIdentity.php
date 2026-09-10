<?php

namespace App\Features\Auth\Data;

final readonly class SocialIdentity
{
    public function __construct(
        public string $provider,
        public string $subject,
        public ?string $email,
        public ?string $name,
        public bool $emailVerified,
    ) {}
}
