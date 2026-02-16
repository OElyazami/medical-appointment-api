import { request } from './client';
import type { Appointment, BookingFormData } from '@/types/models';

export const appointmentsApi = {
    book(data: BookingFormData) {
        return request<{ message: string; data: Appointment }>('/appointments', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },
};
