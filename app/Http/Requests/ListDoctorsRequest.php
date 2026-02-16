<?php

namespace App\Http\Requests;

use App\DataTransferObjects\DoctorFilterDTO;

class ListDoctorsRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialization' => ['sometimes', 'string', 'max:255'],
            'search'         => ['sometimes', 'string', 'max:255'],
            'sort_by'        => ['sometimes', 'string', 'in:name,specialization,created_at'],
            'sort_dir'       => ['sometimes', 'string', 'in:asc,desc'],
            'per_page'       => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDTO(): DoctorFilterDTO
    {
        return new DoctorFilterDTO(
            specialization: $this->validated('specialization'),
            search:         $this->validated('search'),
            sort_by:        $this->validated('sort_by', 'name'),
            sort_dir:       $this->validated('sort_dir', 'asc'),
            per_page:       (int) $this->validated('per_page', 15),
        );
    }
}
