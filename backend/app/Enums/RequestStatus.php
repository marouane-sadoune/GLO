<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'PENDING';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::PENDING => 'في طور المعالجة',
                self::ACCEPTED => 'مقبول',
                self::REJECTED => 'مرفوض',
            },
            default => match ($this) {
                self::PENDING => 'En attente',
                self::ACCEPTED => 'Accepté',
                self::REJECTED => 'Rejeté',
            },
        };
    }
}
