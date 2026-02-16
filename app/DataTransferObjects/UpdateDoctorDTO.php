<?php

namespace App\DataTransferObjects;

readonly class UpdateDoctorDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $specialization = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?bool   $is_active = null,
    ) {}

    /**
     * Return only the fields that were explicitly provided.
     */
    public function toArray(): array
    {
        return array_filter([
            'name'           => $this->name,
            'specialization' => $this->specialization,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'is_active'      => $this->is_active,
        ], fn ($value) => $value !== null);
    }
}
