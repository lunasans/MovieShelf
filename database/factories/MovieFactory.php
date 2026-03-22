<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'year' => fake()->year(),
            'collection_type' => fake()->randomElement(['Blu-ray', 'DVD', '4K', 'Serie']),
            'genre' => implode(', ', fake()->words(3)),
            'runtime' => fake()->numberBetween(60, 240),
            'rating' => fake()->numberBetween(0, 100),
            'rating_age' => fake()->randomElement([0, 6, 12, 16, 18]),
            'overview' => fake()->paragraph(),
            'user_id' => User::factory(),
            'is_deleted' => false,
            'view_count' => 0,
        ];
    }

    public function deleted()
    {
        return $this->state(fn (array $attributes) => [
            'is_deleted' => true,
        ]);
    }
}
