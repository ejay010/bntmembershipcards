<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'tier' => $this->faker->randomElement(['Individual', 'Family', 'Gold', 'Life']),
            'status' => 'active',
            'expires_at' => now()->addYear(),
            'created_by' => User::factory(),
        ];
    }
}
