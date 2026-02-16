<?php

namespace App\Http\Requests;

use App\DataTransferObjects\UpdateDoctorDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'specialization' => ['sometimes', 'required', 'string', 'max:255'],
            'email'          => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('doctors', 'email')->ignore($this->route('doctor'))],
            'phone'          => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }

    public function toDTO(): UpdateDoctorDTO
    {
        $validated = $this->validated();

        return new UpdateDoctorDTO(
            name:           $validated['name'] ?? null,
            specialization: $validated['specialization'] ?? null,
            email:          $validated['email'] ?? null,
            phone:          $validated['phone'] ?? null,
            is_active:      $validated['is_active'] ?? null,
        );
    }
}
