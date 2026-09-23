<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'PENDING';
    case VERIFIED = 'VERIFIED';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::PENDING => 'في طور المعالجة',
                self::VERIFIED => 'محقق',
                self::ACCEPTED => 'مقبول',
                self::REJECTED => 'مرفوض',
            },
            default => match ($this) {
                self::PENDING => 'En attente',
                self::VERIFIED => 'Vérifié',
                self::ACCEPTED => 'Accepté',
                self::REJECTED => 'Rejeté',
            },
        };
    }
}
