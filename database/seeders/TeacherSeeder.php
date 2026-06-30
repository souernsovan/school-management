<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['first_name' => 'Sok',    'last_name' => 'Vanna',   'gender' => 'Male',   'qualification' => 'B.Ed',      'specialization' => 'Mathematics'],
            ['first_name' => 'Sovann',  'last_name' => 'Ratha',   'gender' => 'Female', 'qualification' => 'M.Ed',      'specialization' => 'English'],
            ['first_name' => 'Dara',    'last_name' => 'Chan',    'gender' => 'Male',   'qualification' => 'B.Sc',      'specialization' => 'Science'],
            ['first_name' => 'Srey',    'last_name' => 'Mony',    'gender' => 'Female', 'qualification' => 'B.A',       'specialization' => 'Khmer'],
            ['first_name' => 'Piseth',  'last_name' => 'Kosal',   'gender' => 'Male',   'qualification' => 'B.Ed',      'specialization' => 'Social Studies'],
            ['first_name' => 'Nita',    'last_name' => 'Sopheak', 'gender' => 'Female', 'qualification' => 'B.Sc',      'specialization' => 'Computer'],
            ['first_name' => 'Bunthan', 'last_name' => 'Sokha',   'gender' => 'Male',   'qualification' => 'M.Sc',      'specialization' => 'Physics'],
            ['first_name' => 'Lina',    'last_name' => 'Chann',   'gender' => 'Female', 'qualification' => 'M.Sc',      'specialization' => 'Chemistry'],
            ['first_name' => 'Vuthy',   'last_name' => 'Thida',   'gender' => 'Male',   'qualification' => 'B.Ed',      'specialization' => 'Biology'],
            ['first_name' => 'Sophea',  'last_name' => 'Narin',   'gender' => 'Female', 'qualification' => 'B.P.Ed',    'specialization' => 'Physical Education'],
            ['first_name' => 'Rithy',   'last_name' => 'Sina',    'gender' => 'Male',   'qualification' => 'B.Ed',      'specialization' => 'History'],
            ['first_name' => 'Kanha',   'last_name' => 'Ly',      'gender' => 'Female', 'qualification' => 'M.A',       'specialization' => 'Geography'],
        ];

        foreach ($teachers as $teacher) {
            Teacher::updateOrCreate(
                [
                    'first_name' => $teacher['first_name'],
                    'last_name'  => $teacher['last_name'],
                ],
                [
                    'gender'         => $teacher['gender'],
                    'dob'            => fake()->dateTimeBetween('-55 years', '-25 years')->format('Y-m-d'),
                    'phone'          => fake()->numerify('09########'),
                    'email'          => fake()->unique()->safeEmail(),
                    'qualification'  => $teacher['qualification'],
                    'specialization' => $teacher['specialization'],
                    'hire_date'      => fake()->dateTimeBetween('-15 years', '-1 year')->format('Y-m-d'),
                    'address'        => fake()->city() . ', ' . fake()->state(),
                    'status'         => 'Active',
                ]
            );
        }
    }
}
