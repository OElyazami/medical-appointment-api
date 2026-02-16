<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants for easy reference
     */
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    /**
     * All available statuses
     */
    public static $statuses = [
        self::STATUS_SCHEDULED,
        self::STATUS_CONFIRMED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'doctor_id',
        'patient_name',
        'patient_email',
        'patient_phone',
        'start_time',
        'end_time',
        'status',
        'notes',
        'cancelled_at',
        'cancellation_reason',
        'version',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start_time',
        'end_time',
        'cancelled_at',
    ];

    /**
     * Model events
     */
    protected static function booted()
    {
        // Auto-calculate end_time if not provided
        static::creating(function ($appointment) {
            if (!$appointment->end_time && $appointment->start_time) {
                $appointment->end_time = $appointment->start_time->copy()->addMinutes(30);
            }
            
            // Set default status if not provided
            if (!$appointment->status) {
                $appointment->status = self::STATUS_SCHEDULED;
            }
        });

        // Handle status changes
        static::updating(function ($appointment) {
            if ($appointment->isDirty('status')) {
                // If status is changing to cancelled, set cancelled_at
                if ($appointment->status === self::STATUS_CANCELLED && 
                    !$appointment->cancelled_at) {
                    $appointment->cancelled_at = now();
                }
                
                // Increment version on any update
                $appointment->version = $appointment->version + 1;
            }
        });
    }

    /**
     * Get the doctor that owns the appointment.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Scope a query to only include upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
                     ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope a query to only include past appointments.
     */
    public function scopePast($query)
    {
        return $query->where('start_time', '<', now());
    }

    /**
     * Scope a query to only include today's appointments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    /**
     * Scope a query to filter by date.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('start_time', $date);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('start_time', [$start, $end]);
    }

    /**
     * Scope a query to filter by doctor.
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to exclude cancelled appointments.
     */
    public function scopeNotCancelled($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED]);
    }

    /**
     * Scope a query for appointments that need reminders.
     */
    public function scopeNeedsReminder($query, $hours = 24)
    {
        return $query->where('start_time', '>', now())
                     ->where('start_time', '<=', now()->addHours($hours))
                     ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_CONFIRMED])
                     ->whereDoesntHave('notifications', function ($q) {
                         $q->where('type', 'reminder');
                     });
    }

    /**
     * Check if appointment can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_CONFIRMED]) && 
               $this->start_time->isFuture();
    }

    /**
     * Check if appointment can be rescheduled.
     */
    public function canBeRescheduled(): bool
    {
        return $this->canBeCancelled();
    }

    /**
     * Check if appointment is in the past.
     */
    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }

    /**
     * Check if appointment is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }

    /**
     * Get formatted duration.
     */
    public function getDurationInMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get status badge class for styling.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'badge bg-primary',
            self::STATUS_CONFIRMED => 'badge bg-success',
            self::STATUS_COMPLETED => 'badge bg-secondary',
            self::STATUS_CANCELLED => 'badge bg-danger',
            self::STATUS_NO_SHOW => 'badge bg-warning',
            default => 'badge bg-light',
        };
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
            default => ucfirst($this->status),
        };
    }

    /**
     * Mark appointment as cancelled.
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Mark appointment as completed.
     */
    public function markAsCompleted(): bool
    {
        if ($this->status !== self::STATUS_CONFIRMED && $this->status !== self::STATUS_SCHEDULED) {
            return false;
        }

        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark appointment as no-show.
     */
    public function markAsNoShow(): bool
    {
        if ($this->status !== self::STATUS_SCHEDULED && $this->status !== self::STATUS_CONFIRMED) {
            return false;
        }

        if ($this->end_time->isPast()) {
            return $this->update(['status' => self::STATUS_NO_SHOW]);
        }

        return false;
    }

    /**
     * Confirm the appointment.
     */
    public function confirm(): bool
    {
        if ($this->status !== self::STATUS_SCHEDULED) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CONFIRMED]);
    }
}