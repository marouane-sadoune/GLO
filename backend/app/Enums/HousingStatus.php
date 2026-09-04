<?php

namespace App\Enums;

enum HousingStatus: string
{
    case VACANT = 'VACANT';
    case OCCUPIED = 'OCCUPIED';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::VACANT => 'شاغر',
                self::OCCUPIED => 'عامر',
            },
            default => match ($this) {
                self::VACANT => 'Vacant',
                self::OCCUPIED => 'Occupé',
            },
        };
    }
}
