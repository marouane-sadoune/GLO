<?php

namespace App\Enums;

enum DocumentType: string
{
    case ASSIGNMENT_ORDER = 'ASSIGNMENT_ORDER';
    case COMMITTEE_MINUTES = 'COMMITTEE_MINUTES';
    case COMMITMENT = 'COMMITMENT';
    case INSPECTION_CARD = 'INSPECTION_CARD';
    case NOTIFICATION = 'NOTIFICATION';
    case OTHER = 'OTHER';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::ASSIGNMENT_ORDER => 'قرار التخصيص',
                self::COMMITTEE_MINUTES => 'محضر اللجنة',
                self::COMMITMENT => 'التزام',
                self::INSPECTION_CARD => 'بطاقة المعاينة',
                self::NOTIFICATION => 'إشعار',
                self::OTHER => 'أخرى',
            },
            default => match ($this) {
                self::ASSIGNMENT_ORDER => "Arrêté d'affectation",
                self::COMMITTEE_MINUTES => 'Procès-verbal de commission',
                self::COMMITMENT => 'Engagement',
                self::INSPECTION_CARD => 'Fiche de constat',
                self::NOTIFICATION => 'Notification',
                self::OTHER => 'Autre',
            },
        };
    }
}
