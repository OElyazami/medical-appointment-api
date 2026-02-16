<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 20 random doctors
        Doctor::factory()->count(20)->create();

        Doctor::factory()->count(3)->inactive()->create();
    }
}