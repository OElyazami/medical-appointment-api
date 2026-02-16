<?php

namespace App\DataTransferObjects;

readonly class BookAppointmentDTO
{
    public function __construct(
        public int     $doctor_id,
        public string  $patient_name,
        public string  $start_time,
        public ?string $patient_email = null,
        public ?string $patient_phone = null,
        public ?string $end_time = null,
        public ?string $notes = null,
    ) {}
}
