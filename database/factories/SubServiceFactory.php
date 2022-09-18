<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubService>
 */
class SubServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jobTitle = $this->faker->jobTitle;

        return [
            'name' => $jobTitle,
            'image' => $this->faker->jobTitle,
            'description' => $this->faker->realText,
            'search_tags' => $jobTitle . ',' . $jobTitle . ',' . $jobTitle,
            'min_price' => $this->faker->randomFloat(2, 1, 100),
            'max_price' => $this->faker->randomFloat(2, 1, 100),
            'currency' => $this->faker->currencyCode,
            'slug' => $this->faker->slug,
            'user_id' => $this->faker->randomElement(User::pluck('id')),
            'service_id' => $this->faker->randomElement(Service::pluck('id')),
        ];
    }
}
