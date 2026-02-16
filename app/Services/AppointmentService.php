<?php

namespace App\Services;

use App\DataTransferObjects\BookAppointmentDTO;
use App\Http\Response\ErrorResponse;
use App\Http\Response\IResponseArrayFormat;
use App\Http\Response\SuccessResponse;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;


class AppointmentService
{
    private const SLOT_DURATION = 30;

    /**
     * @return IResponseArrayFormat
     */
    public function book(BookAppointmentDTO $dto): IResponseArrayFormat
    {
        $doctor    = Doctor::findOrFail($dto->doctor_id);
        $startTime = Carbon::parse($dto->start_time);
        $endTime   = $dto->end_time
            ? Carbon::parse($dto->end_time)
            : $startTime->copy()->addMinutes(self::SLOT_DURATION);

        $doctorActive = $this->ensureDoctorIsActive($doctor);
        if (!$doctorActive) {
            return (new ErrorResponse())
                ->setMessage('Cannot book with an inactive doctor.')
                ->setCode(Response::HTTP_FORBIDDEN);
        }

        $isWithinWorkingHours = $this->ensureWithinWorkingHours($doctor, $startTime, $endTime);

        if (!$isWithinWorkingHours) {
            return (new ErrorResponse())
                ->setMessage('Appointment must be within working hours.')
                ->setCode(Response::HTTP_FORBIDDEN);
        }

        return DB::transaction(function () use ($dto, $doctor, $startTime, $endTime) {
            $overlapping = Appointment::where('doctor_id', $doctor->id)
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->notCancelled()
                ->lockForUpdate()
                ->exists();

            if ($overlapping) {
                return (new ErrorResponse())
                    ->setMessage('This time slot is already booked for the selected doctor.')
                    ->setCode(Response::HTTP_CONFLICT);
            }

            Appointment::create([
                'doctor_id'     => $doctor->id,
                'patient_name'  => $dto->patient_name,
                'patient_email' => $dto->patient_email,
                'patient_phone' => $dto->patient_phone,
                'start_time'    => $startTime,
                'end_time'      => $endTime,
                'status'        => Appointment::STATUS_SCHEDULED
            ]);

            return (new SuccessResponse())->setData('Appointment created successfully.')->setCode(Response::HTTP_CREATED);
        });
    }

    private function ensureDoctorIsActive(Doctor $doctor): bool
    {
        if (!$doctor->is_active) {
            return false;
        }
        return true;
    }

    private function ensureWithinWorkingHours(Doctor $doctor, Carbon $startTime, Carbon $endTime): bool
    {
        $dayName = strtolower($startTime->format('l'));
        $hours   = $doctor->working_hours[$dayName] ?? null;

        if (!$hours) {
            if (!$doctor->is_active) {
                return false;
            }
        }

        $dayStart = $startTime->copy()->setTimeFromTimeString($hours[0]);
        $dayEnd   = $startTime->copy()->setTimeFromTimeString($hours[1]);

        if ($startTime->lt($dayStart) || $endTime->gt($dayEnd)) {
            return false;
        }

        return true;
    }
}
