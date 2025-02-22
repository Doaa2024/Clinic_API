<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        $admin = User::factory()->count(1)->create(['role' => 'admin']);


        // Secretaries
        $secretaries = User::factory()->count(5)->create(['role' => 'secretary']);

        // Doctors
        $doctors = User::factory()->count(25)->create(['role' => 'doctor']);
    }
}
