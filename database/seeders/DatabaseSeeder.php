<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RoleAndPermissionSeeder::class);

        // Create admin user (or find existing)
        $admin = User::firstOrCreate(
            ['email' => 'admin@parking.local'],
            [
                'name' => 'Admin User',
                'phone' => '9841000000',
                'password' => bcrypt('password'),
            ]
        );
        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        // Create staff user (or find existing)
        $staff = User::firstOrCreate(
            ['email' => 'staff@parking.local'],
            [
                'name' => 'Staff User',
                'phone' => '9841000001',
                'password' => bcrypt('password'),
            ]
        );
        if (! $staff->hasRole('staff')) {
            $staff->assignRole('staff');
        }

        // Seed vehicle types
        $this->call(VehicleTypeSeeder::class);

        // Seed sample parking tickets
        $this->call(ParkingTicketSeeder::class);
    }
}
