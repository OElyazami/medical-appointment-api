import { request } from './client';
import type { AvailabilityResponse, Doctor, PaginatedResponse } from '@/types/models';

export const doctorsApi = {
    list(params?: Record<string, string>) {
        const qs = params ? '?' + new URLSearchParams(params).toString() : '';
        return request<PaginatedResponse<Doctor>>(`/doctors${qs}`);
    },

    get(id: number) {
        return request<{ data: Doctor }>(`/doctors/${id}`);
    },

    create(data: Partial<Doctor>) {
        return request<{ message: string; data: Doctor }>('/doctors', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },

    update(id: number, data: Partial<Doctor>) {
        return request<{ message: string; data: Doctor }>(`/doctors/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },

    delete(id: number) {
        return request<{ message: string }>(`/doctors/${id}`, {
            method: 'DELETE',
        });
    },

    availability(id: number, date: string) {
        return request<AvailabilityResponse>(`/doctors/${id}/availability?date=${date}`);
    },
};
