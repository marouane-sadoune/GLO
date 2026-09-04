<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the 8 fixed directorates of L'Oriental.
     */
    public function run(): void
    {
        $departments = [
            ['code' => '113', 'name_fr' => 'BERKANE',  'name_ar' => 'بركان'],
            ['code' => '167', 'name_fr' => 'DRIOUCH',  'name_ar' => 'الدريوش'],
            ['code' => '251', 'name_fr' => 'FIGUIG',   'name_ar' => 'فكيك'],
            ['code' => '265', 'name_fr' => 'GUERCIF',  'name_ar' => 'جرسيف'],
            ['code' => '275', 'name_fr' => 'JERADA',   'name_ar' => 'جرادة'],
            ['code' => '381', 'name_fr' => 'NADOR',    'name_ar' => 'الناظور'],
            ['code' => '411', 'name_fr' => 'OUJDA',    'name_ar' => 'وجدة'],
            ['code' => '533', 'name_fr' => 'TAOURIRT', 'name_ar' => 'تاوريرت'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}
