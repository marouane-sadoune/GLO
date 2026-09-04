<?php

namespace App\Enums;

enum OccupantStatus: string
{
    case ACTIVE = 'ACTIVE';
    case RETIRED = 'RETIRED';
    case TRANSFERRED = 'TRANSFERRED';
    case DECEASED = 'DECEASED';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::ACTIVE => 'نشط',
                self::RETIRED => 'متقاعد',
                self::TRANSFERRED => 'منتقل',
                self::DECEASED => 'متوفى',
            },
            default => match ($this) {
                self::ACTIVE => 'Actif',
                self::RETIRED => 'Retraité',
                self::TRANSFERRED => 'Muté',
                self::DECEASED => 'Décédé',
            },
        };
    }
}
