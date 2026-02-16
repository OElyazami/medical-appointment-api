<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'specialization',
        'email',
        'phone',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the appointments for the doctor.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get only active doctors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter doctors by specialization.
     */
    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    /**
     * Get today's appointments for this doctor.
     */
    public function todayAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)
            ->whereDate('start_time', today());
    }

    /**
     * Get upcoming appointments for this doctor.
     */
    public function upcomingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)
            ->where('start_time', '>', now())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->orderBy('start_time');
    }

    /**
     * Check if doctor is available on a given date/time.
     */
    public function isAvailableAt($dateTime): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if within working hours (09:00-17:00, Monday-Friday)
        $carbonTime = \Carbon\Carbon::parse($dateTime);

        return $carbonTime->isWeekday() &&
            $carbonTime->between(
                \Carbon\Carbon::parse('09:00'),
                \Carbon\Carbon::parse('17:00')->subMinutes(30)
            );
    }

    /**
     * Get doctor's working hours as array.
     */
    public function getWorkingHoursAttribute(): array
    {
        return [
            'monday' => ['09:00', '17:00'],
            'tuesday' => ['09:00', '17:00'],
            'wednesday' => ['09:00', '17:00'],
            'thursday' => ['09:00', '17:00'],
            'friday' => ['09:00', '17:00'],
        ];
    }

    /**
     * Get full name with specialization attribute.
     */
    public function getFullNameWithSpecializationAttribute(): string
    {
        return "{$this->name} ({$this->specialization})";
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // Add these helpful methods for working with appointments
    public function getTodayAppointmentsCountAttribute(): int
    {
        return $this->appointments()
            ->whereDate('start_time', today())
            ->count();
    }

    public function getUpcomingAppointmentsCountAttribute(): int
    {
        return $this->appointments()
            ->upcoming()
            ->count();
    }
}
