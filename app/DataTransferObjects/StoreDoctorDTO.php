<?php

namespace App\DataTransferObjects;

readonly class StoreDoctorDTO
{
    public function __construct(
        public string  $name,
        public string  $specialization,
        public ?string $email = null,
        public ?string $phone = null,
        public bool    $is_active = true,
    ) {}

    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'specialization' => $this->specialization,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'is_active'      => $this->is_active,
        ];
    }
}
