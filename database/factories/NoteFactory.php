<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\Wall;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wall_id' => Wall::factory(),
            'content' => fake()->sentence(),
            'author' => fake()->firstName(),
            'author_token' => Str::random(40),
            'color' => fake()->randomElement(Note::COLORS),
            'tags' => [],
            'reactions' => [],
            'pinned' => false,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (): array => ['pinned' => true]);
    }
}
