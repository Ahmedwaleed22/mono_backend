<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->userName(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birth_date' => $this->faker->date(),
            'email' => $this->faker->safeEmail,
            'phone_number' => $this->faker->phoneNumber,
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'gender' => $this->faker->randomElement(["male", "female"]),
            'profession' => $this->faker->jobTitle,
            'account_type' => $this->faker->randomElement(["client", "worker"]),
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
            'password' => Hash::make('123123'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
