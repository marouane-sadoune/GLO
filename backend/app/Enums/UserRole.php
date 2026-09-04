<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case DEPARTMENT_ADMIN = 'DEPARTMENT_ADMIN';
    case ESTABLISHMENT_MANAGER = 'ESTABLISHMENT_MANAGER';

    public function label(string $locale = 'fr'): string
    {
        return match ($locale) {
            'ar' => match ($this) {
                self::SUPER_ADMIN => 'مدير جهوي عام',
                self::DEPARTMENT_ADMIN => 'مدير مديرية إقليمية',
                self::ESTABLISHMENT_MANAGER => 'مدير مؤسسة',
            },
            default => match ($this) {
                self::SUPER_ADMIN => 'Super Administrateur',
                self::DEPARTMENT_ADMIN => 'Administrateur Provincial',
                self::ESTABLISHMENT_MANAGER => "Gestionnaire d'Établissement",
            },
        };
    }
}
