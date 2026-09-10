<?php

namespace App\Features\Shows\Enums;

enum ShowStatus: string
{
    case Current = 'current';
    case Upcoming = 'upcoming';
    case Past = 'past';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Current->value => 'Current',
            self::Upcoming->value => 'Upcoming',
            self::Past->value => 'Past',
        ];
    }
}
