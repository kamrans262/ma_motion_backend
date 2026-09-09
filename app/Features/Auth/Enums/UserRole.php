<?php

namespace App\Features\Auth\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Maker = 'maker';
    case Appreciator = 'appreciator';

    /**
     * Roles that may be selected during public registration.
     *
     * @return list<string>
     */
    public static function registerableValues(): array
    {
        return [
            self::Maker->value,
            self::Appreciator->value,
        ];
    }
}
