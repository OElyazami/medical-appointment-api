<?php

namespace App\Services;

use App\DataTransferObjects\BookAppointmentDTO;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    private const SLOT_DURATION = 30; // minutes

    /**
     * Book a new appointment using pessimistic locking.
     *
     * Wraps the overlap check + insert inside a transaction with
     * SELECT ... FOR UPDATE so that concurrent requests for the
     * same doctor/time are serialised at the database level.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function book(BookAppointmentDTO $dto): Appointment
    {
        $doctor    = Doctor::findOrFail($dto->doctor_id);
        $startTime = Carbon::parse($dto->start_time);
        $endTime   = $dto->end_time
            ? Carbon::parse($dto->end_time)
            : $startTime->copy()->addMinutes(self::SLOT_DURATION);

        $this->ensureDoctorIsActive($doctor);
        $this->ensureWithinWorkingHours($doctor, $startTime, $endTime);

        return DB::transaction(function () use ($dto, $doctor, $startTime, $endTime) {

            // ── Pessimistic lock ──────────────────────────────────
            // Lock all non-cancelled appointment rows for this doctor
            // on the requested date. Any concurrent transaction that
            // reaches this point will block until we commit / rollback.
            $overlapping = Appointment::where('doctor_id', $doctor->id)
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->notCancelled()
                ->lockForUpdate()   // SELECT ... FOR UPDATE
                ->exists();

            if ($overlapping) {
                abort(response()->json([
                    'message' => 'This time slot is already booked for the selected doctor.',
                ], 409));
            }

            return Appointment::create([
                'doctor_id'     => $doctor->id,
                'patient_name'  => $dto->patient_name,
                'patient_email' => $dto->patient_email,
                'patient_phone' => $dto->patient_phone,
                'start_time'    => $startTime,
                'end_time'      => $endTime,
                'status'        => Appointment::STATUS_SCHEDULED,
                'notes'         => $dto->notes,
            ]);
        });
    }

    /**
     * Ensure the doctor is active.
     */
    private function ensureDoctorIsActive(Doctor $doctor): void
    {
        if (!$doctor->is_active) {
            abort(response()->json([
                'message' => 'Cannot book with an inactive doctor.',
            ], 422));
        }
    }

    /**
     * Ensure the requested time falls within the doctor's working hours.
     */
    private function ensureWithinWorkingHours(Doctor $doctor, Carbon $startTime, Carbon $endTime): void
    {
        $dayName = strtolower($startTime->format('l'));
        $hours   = $doctor->working_hours[$dayName] ?? null;

        if (!$hours) {
            abort(response()->json([
                'message' => "Doctor is not available on {$dayName}.",
            ], 422));
        }

        $dayStart = $startTime->copy()->setTimeFromTimeString($hours[0]);
        $dayEnd   = $startTime->copy()->setTimeFromTimeString($hours[1]);

        if ($startTime->lt($dayStart) || $endTime->gt($dayEnd)) {
            abort(response()->json([
                'message' => "Appointment must be within working hours ({$hours[0]}–{$hours[1]}).",
            ], 422));
        }
    }
}
