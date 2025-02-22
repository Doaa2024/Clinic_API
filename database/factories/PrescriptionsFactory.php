<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Appointments;
use App\Models\Doctors;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prescriptions>
 */
class PrescriptionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medication' => json_encode($this->generateRandomMedications()),
            'notes' => fake()->optional()->sentence(4),
        ];
    }
    /**
     * Generate random medications.
     */
    private function generateRandomMedications()
    {
        $medications = [];
        $numberOfMedications = $this->faker->numberBetween(1, 5); // Between 1 and 5 medications

        for ($i = 0; $i < $numberOfMedications; $i++) {
            $medications[] = [
                'name' => $this->faker->word, // Random medication name
                'dose' => $this->faker->randomElement(['250mg', '500mg', '1g']), // Random dose
                'frequency' => $this->faker->randomElement([
                    'Once a day',
                    'Twice a day',
                    'Three times a day',
                    'As needed'
                ]), // Random frequency
            ];
        }

        return $medications;
    }
}
