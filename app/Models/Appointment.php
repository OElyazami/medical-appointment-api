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
        static::creating(function ($appointment) {
            if (!$appointment->end_time && $appointment->start_time) {
                $appointment->end_time = $appointment->start_time->copy()->addMinutes(30);
            }
            
            if (!$appointment->status) {
                $appointment->status = self::STATUS_SCHEDULED;
            }
        });

        static::updating(function ($appointment) {
            if ($appointment->isDirty('status')) {
                if ($appointment->status === self::STATUS_CANCELLED && 
                    !$appointment->cancelled_at) {
                    $appointment->cancelled_at = now();
                }
                $appointment->version = $appointment->version + 1;
            }
        });
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

   
}