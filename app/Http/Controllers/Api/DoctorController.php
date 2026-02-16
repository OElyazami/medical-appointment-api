<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListDoctorsRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function __construct(
        private readonly DoctorService $doctorService,
    ) {}

    /**
     * GET /api/doctors
     */
    public function index(ListDoctorsRequest $request): JsonResponse
    {
        return response()->json(
            $this->doctorService->list($request->toDTO()),
        );
    }

    /**
     * POST /api/doctors
     */
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $doctor = $this->doctorService->create($request->toDTO());

        return response()->json([
            'message' => 'Doctor created successfully.',
            'data'    => $doctor,
        ], 201);
    }

    /**
     * GET /api/doctors/{doctor}
     */
    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json([
            'data' => $doctor,
        ]);
    }

    /**
     * PUT/PATCH /api/doctors/{doctor}
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        $doctor = $this->doctorService->update($doctor, $request->toDTO());

        return response()->json([
            'message' => 'Doctor updated successfully.',
            'data'    => $doctor,
        ]);
    }

    /**
     * DELETE /api/doctors/{doctor}
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        $this->doctorService->delete($doctor);

        return response()->json([
            'message' => 'Doctor deleted successfully.',
        ]);
    }
}
