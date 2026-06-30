<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $firstNames = [
            'Aiden', 'Amara', 'Anita', 'Brian', 'Chenda', 'Daniel', 'Elena', 'Farah',
            'Hannah', 'Ibrahim', 'James', 'Kean', 'Lina', 'Maya', 'Nika', 'Omar',
            'Pheakdey', 'Rith', 'Sokha', 'Tara', 'Visal', 'Yara',
        ];

        $lastNames = [
            'Chan', 'Chheang', 'Heng', 'Kim', 'Lim', 'Nguyen', 'Phan', 'Rin',
            'Seng', 'So', 'Sun', 'Thi', 'Vann', 'Vuth', 'Yang',
        ];

        $classId = SchoolClass::query()->inRandomOrder()->value('id');

        return [
            'first_name' => fake()->randomElement($firstNames),
            'last_name' => fake()->randomElement($lastNames),
            'email' => fake()->unique()->safeEmail(),
            'dob' => fake()->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['Male', 'Female']),
            'phone' => fake()->numerify('09########'),
            'address' => fake()->city() . ', ' . fake()->state(),
            'class_id' => $classId,
        ];
    }
}
