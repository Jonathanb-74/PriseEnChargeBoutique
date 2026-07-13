<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'brand' => $this->faker->randomElement(['Dell', 'HP', 'Lenovo', 'Asus', 'Apple']),
            'model' => $this->faker->bothify('?????-####'),
            'serial_number' => strtoupper($this->faker->bothify('SN-########')),
            'password' => $this->faker->password(),
        ];
    }
}
