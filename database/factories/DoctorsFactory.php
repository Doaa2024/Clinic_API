<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctors>
 */
class DoctorsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specialty' => fake()->randomElement(['Cardiology', 'Pediatrics', 'Dermatology', 'Orthopedics']),
            'availability' => json_encode([
                'Monday' => $this->generateRandomAvailability(),
                'Tuesday' => $this->generateRandomAvailability(),
                'Wednesday' => $this->generateRandomAvailability(),
                'Thursday' => $this->generateRandomAvailability(),
                'Friday' => $this->generateRandomAvailability(),
            ]),
        ];
    }
    private function generateRandomAvailability()
    {
        $startTime = $this->faker->time('H:i A', '14:00'); // Random start time before 2 PM
        $endTime = $this->faker->time('H:i A', '18:00'); // Random end time before 6 PM

        return "$startTime - $endTime";
    }
}
