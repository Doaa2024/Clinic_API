<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Appointments;
use App\Models\Prescriptions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointments = Appointments::all();
        foreach ($appointments as $appointment) {
            Billing::factory()->create(['appointment_id' => $appointment->id]);
        }
    }
}
