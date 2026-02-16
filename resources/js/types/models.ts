export interface Doctor {
    id: number;
    name: string;
    specialization: string;
    email: string | null;
    phone: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface Appointment {
    id: number;
    doctor_id: number;
    patient_name: string;
    patient_email: string | null;
    patient_phone: string | null;
    start_time: string;
    end_time: string;
    status: string;
    notes: string | null;
    version: number;
    doctor?: Doctor;
    created_at: string;
    updated_at: string;
}

export interface Slot {
    start_time: string;
    end_time: string;
}

export interface AvailabilityResponse {
    doctor: Pick<Doctor, 'id' | 'name' | 'specialization'>;
    date: string;
    slots: Slot[];
}

export interface DoctorFormData {
    name: string;
    specialization: string;
    email: string;
    phone: string;
}

export interface BookingFormData {
    doctor_id: number;
    patient_name: string;
    patient_email: string;
    patient_phone: string;
    start_time: string;
    notes: string;
}
