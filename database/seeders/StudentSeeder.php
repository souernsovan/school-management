<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(300)
            ->create()
            ->each(function (User $user) {
                $user->assignRole('Student');

                $nameParts = preg_split('/\s+/', trim($user->name), 2);
                $firstName = $nameParts[0] ?: fake()->firstName();
                $lastName = $nameParts[1] ?? fake()->lastName();

                $classId = SchoolClass::query()->inRandomOrder()->value('id');

                Student::updateOrCreate(
                    ['email' => $user->email],
                    [
                        'first_name' => Str::of($firstName)->title(),
                        'last_name'  => Str::of($lastName)->title(),
                        'dob'        => fake()->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
                        'gender'     => fake()->randomElement(['Male', 'Female']),
                        'phone'      => fake()->numerify('09########'),
                        'address'    => fake()->city() . ', ' . fake()->state(),
                        'class_id'   => $classId,
                    ]
                );
            });
    }
}
