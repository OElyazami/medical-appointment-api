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
        if (Doctor::count() > 0) {
            $this->command?->info('Doctors table already seeded, skipping.');
            return;
        }

        // Create 20 random doctors
        Doctor::factory()->count(20)->create();

        Doctor::factory()->count(3)->inactive()->create();
    }
}