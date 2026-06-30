<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(7, 12) as $name) {
            foreach (['A', 'B', 'C'] as $section) {
                SchoolClass::updateOrCreate(
                    ['name' => (string) $name, 'section' => $section],
                    ['description' => "Class {$name} {$section}"]
                );
            }
        }
    }
}
