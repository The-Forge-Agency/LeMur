<?php

namespace Database\Factories;

use App\Models\Wall;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Wall>
 */
class WallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => strtolower(Str::ulid()->toBase32()),
            'admin_token' => Str::random(48),
            'name' => fake()->words(3, true),
            'pin_hash' => null,
            'read_only' => false,
        ];
    }

    public function withPin(string $pin = '1234'): static
    {
        return $this->state(fn (): array => ['pin_hash' => Hash::make($pin)]);
    }

    public function readOnly(): static
    {
        return $this->state(fn (): array => ['read_only' => true]);
    }
}
