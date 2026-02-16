<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListDoctorsRequest;
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function __construct(
        private readonly DoctorService $doctorService,
    ) {}

    public function index(ListDoctorsRequest $request): JsonResponse
    {
        return new JsonResponse($this->doctorService->list($request->toDTO()));
    }
}
