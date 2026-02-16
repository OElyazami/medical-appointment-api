<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookAppointmentRequest;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
    ) {}

    /**
     * POST /api/appointments
     */
    public function store(BookAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->appointmentService->book($request->toDTO());

        return response()->json([
            'message' => 'Appointment booked successfully.',
            'data'    => $appointment->load('doctor'),
        ], 201);
    }
}
