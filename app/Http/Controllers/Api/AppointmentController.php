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

    public function book(BookAppointmentRequest $request): JsonResponse
    {
        $response = $this->appointmentService->book($request->toDTO());

        return new JsonResponse($response->getArrayFormat())->setStatusCode($response->getCode());
    }
}
