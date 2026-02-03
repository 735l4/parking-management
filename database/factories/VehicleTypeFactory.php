<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleType>
 */
class VehicleTypeFactory extends Factory
{
    protected $model = VehicleType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['name' => 'Two Wheeler', 'hourly' => 20, 'min' => 20],
            ['name' => 'Four Wheeler', 'hourly' => 40, 'min' => 40],
            ['name' => 'Heavy Vehicle', 'hourly' => 80, 'min' => 80],
            ['name' => 'Bicycle', 'hourly' => 10, 'min' => 10],
        ];

        $type = $this->faker->randomElement($types);

        return [
            'name' => $type['name'],
            'hourly_rate' => $type['hourly'],
            'minimum_charge' => $type['min'],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the vehicle type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a two wheeler type.
     */
    public function twoWheeler(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Two Wheeler',
            'hourly_rate' => 20,
            'minimum_charge' => 20,
        ]);
    }

    /**
     * Create a four wheeler type.
     */
    public function fourWheeler(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Four Wheeler',
            'hourly_rate' => 40,
            'minimum_charge' => 40,
        ]);
    }

    /**
     * Create a heavy vehicle type.
     */
    public function heavyVehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Heavy Vehicle',
            'hourly_rate' => 80,
            'minimum_charge' => 80,
        ]);
    }
}
