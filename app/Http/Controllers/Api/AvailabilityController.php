<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availabilityService,
    ) {}

    /**
     * GET /api/doctors/{doctor}/availability?date=YYYY-MM-DD
     */
    public function __invoke(Request $request, Doctor $doctor): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $result = $this->availabilityService->getAvailableSlots(
            $doctor,
            $request->input('date'),
        );

        return response()->json($result);
    }
}
