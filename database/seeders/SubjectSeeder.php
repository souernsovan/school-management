<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathematics',       'code' => 'MATH',  'credit' => 4],
            ['name' => 'English',           'code' => 'ENG',   'credit' => 4],
            ['name' => 'Khmer',             'code' => 'KHM',   'credit' => 4],
            ['name' => 'Science',           'code' => 'SCI',   'credit' => 4],
            ['name' => 'Social Studies',    'code' => 'SOC',   'credit' => 3],
            ['name' => 'Computer',          'code' => 'ICT',   'credit' => 3],
            ['name' => 'History',           'code' => 'HIS',   'credit' => 3],
            ['name' => 'Geography',         'code' => 'GEO',   'credit' => 3],
            ['name' => 'Physics',           'code' => 'PHY',   'credit' => 4],
            ['name' => 'Chemistry',         'code' => 'CHEM',  'credit' => 4],
            ['name' => 'Biology',           'code' => 'BIO',   'credit' => 4],
            ['name' => 'Physical Education','code' => 'PE',    'credit' => 2],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                [
                    'name'   => $subject['name'],
                    'credit' => $subject['credit'],
                ]
            );
        }
    }
}
