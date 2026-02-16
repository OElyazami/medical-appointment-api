<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreDoctorDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'specialization' => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255', 'unique:doctors,email'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }

    public function toDTO(): StoreDoctorDTO
    {
        return new StoreDoctorDTO(
            name:           $this->validated('name'),
            specialization: $this->validated('specialization'),
            email:          $this->validated('email'),
            phone:          $this->validated('phone'),
            is_active:      $this->validated('is_active', true),
        );
    }
}
