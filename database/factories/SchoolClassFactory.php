<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $names = ['7', '8', '9', '10', '11', '12'];
        $sections = ['A', 'B', 'C'];

        return [
            'name' => fake()->randomElement($names),
            'section' => fake()->randomElement($sections),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
