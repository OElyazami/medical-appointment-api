<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    private const SLOT_DURATION = 30; // minutes

    /**
     * Get available slots for a doctor on a given date.
     *
     * @return array{doctor: array, date: string, slots: Collection}
     *
     * @throws \App\Exceptions\DoctorNotAvailableException
     */
    public function getAvailableSlots(Doctor $doctor, string $date): array
    {
        $date = Carbon::parse($date);

        $this->ensureDoctorIsActive($doctor);

        $dayName = strtolower($date->format('l'));
        $hours   = $doctor->working_hours[$dayName] ?? null;

        $this->ensureHasWorkingHours($hours, $dayName);

        $allSlots           = $this->generateSlots($date, $hours[0], $hours[1]);
        $bookedAppointments = $this->getBookedAppointments($doctor, $date);
        $availableSlots     = $this->filterAvailableSlots($allSlots, $bookedAppointments);

        return [
            'doctor' => $doctor->only('id', 'name', 'specialization'),
            'date'   => $date->toDateString(),
            'slots'  => $availableSlots,
        ];
    }

    /**
     * Generate all 30-minute slots between working hours.
     */
    private function generateSlots(Carbon $date, string $dayStart, string $dayEnd): array
    {
        $start = $date->copy()->setTimeFromTimeString($dayStart);
        $end   = $date->copy()->setTimeFromTimeString($dayEnd);

        $slots  = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $slots[] = [
                'start' => $cursor->copy(),
                'end'   => $cursor->copy()->addMinutes(self::SLOT_DURATION),
            ];
            $cursor->addMinutes(self::SLOT_DURATION);
        }

        return $slots;
    }

    /**
     * Fetch non-cancelled appointments for a doctor on a given date.
     */
    private function getBookedAppointments(Doctor $doctor, Carbon $date): Collection
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->whereDate('start_time', $date)
            ->notCancelled()
            ->get(['start_time', 'end_time']);
    }

    /**
     * Remove slots that overlap with booked appointments.
     */
    private function filterAvailableSlots(array $allSlots, Collection $bookedAppointments): Collection
    {
        return collect($allSlots)->filter(function (array $slot) use ($bookedAppointments) {
            foreach ($bookedAppointments as $appt) {
                if ($slot['start']->lt($appt->end_time) && $slot['end']->gt($appt->start_time)) {
                    return false;
                }
            }
            return true;
        })->values()->map(fn (array $slot) => [
            'start_time' => $slot['start']->format('H:i'),
            'end_time'   => $slot['end']->format('H:i'),
        ]);
    }

    /**
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    private function ensureDoctorIsActive(Doctor $doctor): void
    {
        if (!$doctor->is_active) {
            abort(response()->json([
                'message' => 'Doctor is not currently active.',
                'data'    => [],
            ], 422));
        }
    }

    /**
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    private function ensureHasWorkingHours(?array $hours, string $dayName): void
    {
        if (!$hours) {
            abort(response()->json([
                'message' => "Doctor is not available on {$dayName}.",
                'data'    => [],
            ], 200));
        }
    }
}
