<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Actor>
 */
class ActorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_year' => fake()->year(),
            'birthday' => fake()->date(),
            'place_of_birth' => fake()->city().', '.fake()->country(),
            'bio' => fake()->paragraphs(3, true),
            'slug' => Str::slug($firstName.' '.$lastName),
        ];
    }
}
