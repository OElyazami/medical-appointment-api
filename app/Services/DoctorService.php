<?php

namespace App\Services;

use App\DataTransferObjects\DoctorFilterDTO;
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
}
