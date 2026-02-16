import { useState, useEffect } from 'react';
import type { Doctor, BookingFormData, Slot } from '@/types/models';
import { appointmentsApi, doctorsApi, ApiError } from '@/lib/api';

interface Props {
    doctors: Doctor[];
    selectedDoctor: Doctor | null;
    onBooked: (message: string) => void;
    onError: (message: string) => void;
}

function todayStr(): string {
    return new Date().toISOString().split('T')[0];
}

export function BookingForm({ doctors, selectedDoctor, onBooked, onError }: Props) {
    const [doctorId, setDoctorId] = useState<number>(selectedDoctor?.id ?? 0);
    const [date, setDate] = useState(todayStr());
    const [slots, setSlots] = useState<Slot[]>([]);
    const [selectedSlot, setSelectedSlot] = useState('');
    const [slotsLoading, setSlotsLoading] = useState(false);

    const [form, setForm] = useState<Omit<BookingFormData, 'doctor_id' | 'start_time'>>({
        patient_name: '',
        patient_email: '',
        patient_phone: '',
        notes: '',
    });
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [loading, setLoading] = useState(false);

    // Load slots when doctor or date changes
    useEffect(() => {
        if (!doctorId || !date) {
            setSlots([]);
            setSelectedSlot('');
            return;
        }

        let cancelled = false;

        async function fetchSlots() {
            setSlotsLoading(true);
            setSlots([]);
            setSelectedSlot('');

            try {
                const res = await doctorsApi.availability(doctorId, date);
                if (!cancelled) {
                    setSlots(res.slots);
                }
            } catch (err) {
                if (!cancelled && err instanceof ApiError) {
                    setSlots([]);
                }
            } finally {
                if (!cancelled) setSlotsLoading(false);
            }
        }

        fetchSlots();
        return () => { cancelled = true; };
    }, [doctorId, date]);

    function updateField(field: keyof typeof form, value: string) {
        setForm((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => {
            const next = { ...prev };
            delete next[field];
            return next;
        });
    }

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();

        if (!doctorId) {
            onError('Please select a doctor');
            return;
        }
        if (!selectedSlot) {
            onError('Please select a time slot');
            return;
        }

        setLoading(true);
        setErrors({});

        const startTime = `${date} ${selectedSlot}:00`;

        try {
            const res = await appointmentsApi.book({
                doctor_id: doctorId,
                patient_name: form.patient_name,
                patient_email: form.patient_email,
                patient_phone: form.patient_phone,
                start_time: startTime,
                notes: form.notes,
            });
            onBooked(res.message);
        } catch (err) {
            if (err instanceof ApiError && err.errors) {
                console.log(err)
                setErrors(err.errors);
            } else if (err instanceof ApiError) {
                onError(err.message);
                console.log(err)
            } else {
                onError('An unexpected error occurred');
            }
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="mx-auto max-w-lg">
            <h2 className="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Book Appointment</h2>

            <form
                onSubmit={handleSubmit}
                className="space-y-5 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                {/* Doctor */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Doctor *
                    </label>
                    <select
                        value={doctorId || ''}
                        onChange={(e) => setDoctorId(Number(e.target.value))}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        required
                    >
                        <option value="">Select a doctor</option>
                        {doctors.map((d) => (
                            <option key={d.id} value={d.id}>
                                {d.name} — {d.specialization}
                            </option>
                        ))}
                    </select>
                    {errors.doctor_id && <p className="mt-1 text-xs text-red-600">{errors.doctor_id[0]}</p>}
                </div>

                {/* Date */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date *
                    </label>
                    <input
                        type="date"
                        value={date}
                        onChange={(e) => setDate(e.target.value)}
                        min={todayStr()}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        required
                    />
                </div>

                {/* Time slot */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Time Slot *
                    </label>
                    {slotsLoading ? (
                        <p className="text-sm text-gray-500">Loading available slots...</p>
                    ) : slots.length === 0 && doctorId ? (
                        <p className="text-sm text-yellow-600 dark:text-yellow-400">
                            No slots available for this date.
                        </p>
                    ) : (
                        <div className="grid grid-cols-4 gap-2">
                            {slots.map((slot) => (
                                <button
                                    key={slot.start_time}
                                    type="button"
                                    onClick={() => setSelectedSlot(slot.start_time)}
                                    className={`rounded-lg border px-2 py-2 text-center text-xs font-medium transition-all ${
                                        selectedSlot === slot.start_time
                                            ? 'border-blue-500 bg-blue-600 text-white'
                                            : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-blue-600'
                                    }`}
                                >
                                    {slot.start_time}
                                </button>
                            ))}
                        </div>
                    )}
                    {errors.start_time && <p className="mt-1 text-xs text-red-600">{errors.start_time[0]}</p>}
                </div>

                <hr className="border-gray-200 dark:border-gray-700" />

                {/* Patient Name */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Patient Name *
                    </label>
                    <input
                        type="text"
                        value={form.patient_name}
                        onChange={(e) => updateField('patient_name', e.target.value)}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="John Doe"
                        required
                    />
                    {errors.patient_name && (
                        <p className="mt-1 text-xs text-red-600">{errors.patient_name[0]}</p>
                    )}
                </div>

                {/* Patient Email */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Patient Email
                    </label>
                    <input
                        type="email"
                        value={form.patient_email}
                        onChange={(e) => updateField('patient_email', e.target.value)}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="john@example.com"
                    />
                    {errors.patient_email && (
                        <p className="mt-1 text-xs text-red-600">{errors.patient_email[0]}</p>
                    )}
                </div>

                {/* Patient Phone */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Patient Phone
                    </label>
                    <input
                        type="text"
                        value={form.patient_phone}
                        onChange={(e) => updateField('patient_phone', e.target.value)}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="+1234567890"
                    />
                    {errors.patient_phone && (
                        <p className="mt-1 text-xs text-red-600">{errors.patient_phone[0]}</p>
                    )}
                </div>

                {/* Notes */}
                <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes
                    </label>
                    <textarea
                        value={form.notes}
                        onChange={(e) => updateField('notes', e.target.value)}
                        rows={3}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        placeholder="Any additional notes..."
                    />
                    {errors.notes && <p className="mt-1 text-xs text-red-600">{errors.notes[0]}</p>}
                </div>

                {/* Submit */}
                <button
                    type="submit"
                    disabled={loading || !selectedSlot}
                    className="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                >
                    {loading ? 'Booking...' : 'Book Appointment'}
                </button>
            </form>
        </div>
    );
}
