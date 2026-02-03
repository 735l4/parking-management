<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = [
            [
                'name' => 'Two Wheeler',
                'hourly_rate' => 20.00,
                'minimum_charge' => 20.00,
                'is_active' => true,
            ],
            [
                'name' => 'Four Wheeler',
                'hourly_rate' => 40.00,
                'minimum_charge' => 40.00,
                'is_active' => true,
            ],
            [
                'name' => 'Heavy Vehicle',
                'hourly_rate' => 80.00,
                'minimum_charge' => 80.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bicycle',
                'hourly_rate' => 10.00,
                'minimum_charge' => 10.00,
                'is_active' => true,
            ],
        ];

        foreach ($vehicleTypes as $type) {
            VehicleType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
