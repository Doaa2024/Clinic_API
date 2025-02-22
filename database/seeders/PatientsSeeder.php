<?php

namespace Database\Seeders;

use App\Models\Patients;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Patients::factory()->count(100)->create();
    }
}
