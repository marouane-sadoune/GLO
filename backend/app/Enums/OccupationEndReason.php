<?php

namespace App\Enums;

enum OccupationEndReason: string
{
    case VACATION = 'VACATION';
    case TRANSFER = 'TRANSFER';
    case RETIREMENT = 'RETIREMENT';
    case DEATH = 'DEATH';
    case ADMINISTRATIVE = 'ADMINISTRATIVE';
    case OTHER = 'OTHER';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::VACATION => 'إفراغ',
                self::TRANSFER => 'انتقال',
                self::RETIREMENT => 'تقاعد',
                self::DEATH => 'وفاة',
                self::ADMINISTRATIVE => 'إداري',
                self::OTHER => 'أخرى',
            },
            default => match ($this) {
                self::VACATION => 'Vacation',
                self::TRANSFER => 'Mutation',
                self::RETIREMENT => 'Retraite',
                self::DEATH => 'Décès',
                self::ADMINISTRATIVE => 'Administratif',
                self::OTHER => 'Autre',
            },
        };
    }
}
