<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to doctors table
            $table->foreignId('doctor_id')
                  ->constrained('doctors')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // Patient information
            $table->string('patient_name');
            $table->string('patient_email')->nullable();
            $table->string('patient_phone')->nullable();
            
            // Appointment times
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            
            // Status with default value
            $table->enum('status', [
                'scheduled', 
                'confirmed', 
                'completed', 
                'cancelled', 
                'no_show'
            ])->default('scheduled');
            
            // Additional fields
            $table->text('notes')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Version for optimistic locking
            $table->unsignedInteger('version')->default(1);
            
            $table->timestamps();
            $table->softDeletes(); // Adds deleted_at column
            
            // Indexes for performance
            $table->index('patient_name');
            $table->index('patient_email');
            $table->index('status');
            $table->index('start_time');
            $table->index('end_time');
            
            // Composite indexes for common queries
            $table->index(['doctor_id', 'start_time', 'status']);
            $table->index(['doctor_id', 'start_time', 'end_time']);
            
            // Unique constraint to prevent double-booking at database level
            $table->unique(['doctor_id', 'start_time'], 'unique_doctor_appointment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};