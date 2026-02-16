import { useState } from 'react';
import type { Doctor, DoctorFormData } from '@/types/models';
import { doctorsApi, ApiError } from '@/lib/api';

interface Props {
    doctor: Doctor | null;
    onSaved: () => void;
    onCancel: () => void;
    onError: (message: string) => void;
}

const SPECIALIZATIONS = [
    'Cardiology',
    'Dermatology',
    'Pediatrics',
    'Orthopedics',
    'Neurology',
    'General Practice',
    'Ophthalmology',
    'Gynecology',
    'Psychiatry',
    'Radiology',
];

export function DoctorForm({ doctor, onSaved, onCancel, onError }: Props) {
    const isEditing = !!doctor;

    const [form, setForm] = useState<DoctorFormData>({
        name: doctor?.name ?? '',
        specialization: doctor?.specialization ?? '',
        email: doctor?.email ?? '',
        phone: doctor?.phone ?? '',
    });
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [loading, setLoading] = useState(false);

    function updateField(field: keyof DoctorFormData, value: string) {
        setForm((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
    }

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            if (isEditing) {
                await doctorsApi.update(doctor.id, form);
            } else {
                await doctorsApi.create(form);
            }
            onSaved();
        } catch (err) {
            if (err instanceof ApiError && err.errors) {
                setErrors(err.errors);
            } else if (err instanceof ApiError) {
                onError(err.message);
            } else {
                onError('An unexpected error occurred');
            }
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="mx-auto max-w-lg">
            <h2 className="mb-6 text-lg font-semibold text-gray-900 dark:text-white">
                {isEditing ? 'Edit Doctor' : 'Add Doctor'}
            </h2>

            <form
                onSubmit={handleSubmit}
                className="space-y-5 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                {/* Name */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Name *
                    </label>
                    <input
                        type="text"
                        value={form.name}
                        onChange={(e) => updateField('name', e.target.value)}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="Dr. John Smith"
                        required
                    />
                    {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name[0]}</p>}
                </div>

                {/* Specialization */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Specialization *
                    </label>
                    <select
                        value={form.specialization}
                        onChange={(e) => updateField('specialization', e.target.value)}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        required
                    >
                        <option value="">Select specialization</option>
                        {SPECIALIZATIONS.map((s) => (
                            <option key={s} value={s}>
                                {s}
                            </option>
                        ))}
                    </select>
                    {errors.specialization && (
                        <p className="mt-1 text-xs text-red-600">{errors.specialization[0]}</p>
                    )}
                </div>

                {/* Email */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email
                    </label>
                    <input
                        type="email"
                        value={form.email}
                        onChange={(e) => updateField('email', e.target.value)}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="doctor@example.com"
                    />
                    {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email[0]}</p>}
                </div>

                {/* Phone */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Phone
                    </label>
                    <input
                        type="text"
                        value={form.phone}
                        onChange={(e) => updateField('phone', e.target.value)}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="+1234567890"
                    />
                    {errors.phone && <p className="mt-1 text-xs text-red-600">{errors.phone[0]}</p>}
                </div>

                {/* Buttons */}
                <div className="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={loading}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {loading ? 'Saving...' : isEditing ? 'Update Doctor' : 'Create Doctor'}
                    </button>
                    <button
                        type="button"
                        onClick={onCancel}
                        className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    );
}
