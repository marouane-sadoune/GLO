<?php

namespace App\Enums;

enum HousingCategory: string
{
    case ADMINISTRATIVE = 'ADMINISTRATIVE';
    case FUNCTIONAL = 'FUNCTIONAL';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::ADMINISTRATIVE => 'سكن إداري',
                self::FUNCTIONAL => 'سكن وظيفي',
            },
            default => match ($this) {
                self::ADMINISTRATIVE => 'Logement administratif',
                self::FUNCTIONAL => 'Logement fonctionnel',
            },
        };
    }
}
