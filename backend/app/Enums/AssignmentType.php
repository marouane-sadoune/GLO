<?php

namespace App\Enums;

enum AssignmentType: string
{
    case MANDATORY = 'MANDATORY';
    case FREE = 'FREE';
    case BY_LAW = 'BY_LAW';
    case ACTUAL = 'ACTUAL';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::MANDATORY => 'المسكنون وجوباً',
                self::FREE => 'بالمجان',
                self::BY_LAW => 'بحكم القانون',
                self::ACTUAL => 'بالفعل',
            },
            default => match ($this) {
                self::MANDATORY => 'Obligatoire',
                self::FREE => 'À titre gracieux',
                self::BY_LAW => 'De droit',
                self::ACTUAL => 'De fait',
            },
        };
    }
}
