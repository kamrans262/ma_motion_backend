<?php

namespace App\Features\Auth\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
