<?php

namespace Database\Seeders;

use App\Models\Doctors;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'doctor')->get();
        foreach ($users as $user) {
            Doctors::factory()->create(['doctor_id'=>$user->id]);
        }
    }
}
