<?php

namespace App\Enums;

enum OccupationStatus: string
{
    case ACTIVE = 'ACTIVE';
    case ENDED = 'ENDED';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::ACTIVE => 'نشط',
                self::ENDED => 'منتهي',
            },
            default => match ($this) {
                self::ACTIVE => 'Actif',
                self::ENDED => 'Clôturé',
            },
        };
    }
}
