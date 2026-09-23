<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case DP_AGENT = 'DP_AGENT';
    case ESTABLISHMENT_MANAGER = 'ESTABLISHMENT_MANAGER';
    case AREF_VALIDATOR = 'AREF_VALIDATOR';
    case AREF_DIRECTOR = 'AREF_DIRECTOR';

    /**
     * Roles whose scope is the whole region (all 8 provinces), not a single
     * department or establishment — same visibility as SUPER_ADMIN.
     */
    public function isRegionWide(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::AREF_VALIDATOR, self::AREF_DIRECTOR], true);
    }

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::SUPER_ADMIN => 'مدير جهوي عام',
                self::DP_AGENT => 'ممثل المديرية الإقليمية',
                self::ESTABLISHMENT_MANAGER => 'مدير مؤسسة',
                self::AREF_VALIDATOR => 'مسؤول الأكاديمية الجهوية',
                self::AREF_DIRECTOR => 'مدير الأكاديمية الجهوية',
            },
            default => match ($this) {
                self::SUPER_ADMIN => 'Super Administrateur',
                self::DP_AGENT => 'Agent de la Direction Provinciale',
                self::ESTABLISHMENT_MANAGER => "Gestionnaire d'Établissement",
                self::AREF_VALIDATOR => "Validateur de l'AREF",
                self::AREF_DIRECTOR => "Directeur de l'AREF",
            },
        };
    }
}
