<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Prescriptions;
use App\Models\Appointments;
use App\Models\Doctors;

class PrescriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointments = Appointments::all();
        foreach ($appointments as $appointment) {
            Prescriptions::factory()->create(['appointment_id' => $appointment->id, 'doctor_id' => $appointment->doctor_id]);
        }
    }
}
