<?php

namespace App\Services;

use App\DataTransferObjects\DoctorFilterDTO;
use App\DataTransferObjects\StoreDoctorDTO;
use App\DataTransferObjects\UpdateDoctorDTO;
use App\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DoctorService
{
    /**
     * List doctors with optional filters, search, sorting, and pagination.
     */
    public function list(DoctorFilterDTO $filters): LengthAwarePaginator
    {
        $query = Doctor::query()->active();

        if ($filters->specialization) {
            $query->bySpecialization($filters->specialization);
        }

        if ($filters->search) {
            $query->where('name', 'ilike', '%' . $filters->search . '%');
        }

        $allowedSorts = ['name', 'specialization', 'created_at'];

        if (in_array($filters->sort_by, $allowedSorts)) {
            $query->orderBy($filters->sort_by, $filters->sort_dir);
        }

        return $query->paginate($filters->per_page);
    }

    /**
     * Create a new doctor.
     */
    public function create(StoreDoctorDTO $dto): Doctor
    {
        return Doctor::create($dto->toArray());
    }

    /**
     * Update an existing doctor.
     */
    public function update(Doctor $doctor, UpdateDoctorDTO $dto): Doctor
    {
        $doctor->update($dto->toArray());

        return $doctor->fresh();
    }

    /**
     * Soft-delete a doctor.
     */
    public function delete(Doctor $doctor): void
    {
        $doctor->delete();
    }
}
