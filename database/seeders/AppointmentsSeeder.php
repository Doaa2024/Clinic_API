<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointments;
use App\Models\Doctors;
use App\Models\Patients;
use App\Models\User;

class AppointmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctors::all();
        $patients = Patients::all();
        $created_by = User::where('role', 'secretary')->get();
        foreach ($patients as $patient) {
            $numberofAppointments = rand(1, 5);
            for ($i = 0; $i < $numberofAppointments; $i++) {
                Appointments::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctors->random(), 'created_by' => $created_by->random()]);
            }
        }
    }
}
