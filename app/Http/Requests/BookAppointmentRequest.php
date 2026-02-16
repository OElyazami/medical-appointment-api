<?php

namespace App\Http\Requests;

use App\DataTransferObjects\BookAppointmentDTO;

class BookAppointmentRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id'     => ['required', 'integer', 'exists:doctors,id'],
            'patient_name'  => ['required', 'string', 'max:255'],
            'patient_email' => ['nullable', 'email', 'max:255'],
            'patient_phone' => ['nullable', 'string', 'max:50'],
            'start_time'    => ['required', 'date', 'after:now'],
            'end_time'      => ['nullable', 'date', 'after:start_time'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }
    

    public function toDTO(): BookAppointmentDTO
    {
        return new BookAppointmentDTO(
            doctor_id:     $this->validated('doctor_id'),
            patient_name:  $this->validated('patient_name'),
            start_time:    $this->validated('start_time'),
            patient_email: $this->validated('patient_email'),
            patient_phone: $this->validated('patient_phone'),
            end_time:      $this->validated('end_time'),
            notes:         $this->validated('notes'),
        );
    }
}
