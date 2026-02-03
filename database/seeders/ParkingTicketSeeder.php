<?php

namespace Database\Seeders;

use App\Models\ParkingTicket;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class ParkingTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = VehicleType::all();
        $users = User::all();

        if ($vehicleTypes->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Please seed vehicle types and users first.');

            return;
        }

        // Create tickets one by one to ensure unique ticket numbers
        // Create some currently parked vehicles
        for ($i = 0; $i < 5; $i++) {
            ParkingTicket::factory()
                ->parked()
                ->today()
                ->recycle($vehicleTypes)
                ->recycle($users)
                ->create();
        }

        // Create some exited vehicles from today
        for ($i = 0; $i < 10; $i++) {
            ParkingTicket::factory()
                ->exited()
                ->today()
                ->recycle($vehicleTypes)
                ->recycle($users)
                ->create();
        }

        // Create some exited vehicles from the past week
        for ($i = 0; $i < 30; $i++) {
            ParkingTicket::factory()
                ->exited()
                ->recycle($vehicleTypes)
                ->recycle($users)
                ->create();
        }
    }
}
