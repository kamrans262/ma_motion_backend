<?php

namespace App\Features\Notifications\Enums;

enum NotificationType: string
{
    case SavedMakerShow = 'saved_maker_show';

    public function label(): string
    {
        return match ($this) {
            self::SavedMakerShow => 'Saved Maker Show',
        };
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(static fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
