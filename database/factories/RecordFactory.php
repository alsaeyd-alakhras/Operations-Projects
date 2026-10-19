<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Record>
 */
class RecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'name' => $this->faker->name(),
            'financier_number' => rand(1,100),
            'age' => $this->faker->numberBetween(1, 80),
            'patient_ID' => $this->faker->randomNumber(6, true),
            'phone_number1' => '0594318545',
            'phone_number2' => '0594318545',
            'operation' => $this->faker->word(),
            'doctor' => $this->faker->word(),
            'amount' => $this->faker->numberBetween(1, 1000),
            'doctor_share' => $this->faker->numberBetween(1, 100),
            'anesthesiologists_share' => $this->faker->numberBetween(1, 100),
            'anesthesia' => $this->faker->numberBetween(1, 100),
            'bed' => $this->faker->numberBetween(1, 100),
            'private' => $this->faker->numberBetween(1, 100),
            'done' => $this->faker->boolean(),
            'notes' => $this->faker->sentence(),
            'displacement_status' => $this->faker->randomElement(array_keys(\App\Models\Record::DISPLACEMENT_STATUSES)),
            'income_status' => $this->faker->randomElement(array_keys(\App\Models\Record::INCOME_STATUSES)),
            'family_burden' => $this->faker->randomElement(array_keys(\App\Models\Record::FAMILY_BURDENS)),
            'distributed' => $this->faker->boolean(),
            'branch_id' => null,
            'eligible' => null,
            'branch_notes' => null,
            'user_id' => 1,
            'user_name' => 'saeyd_jamal',
        ];
    }
}
