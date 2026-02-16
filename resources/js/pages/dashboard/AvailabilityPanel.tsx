import { useState, useEffect } from 'react';
import type { Doctor, Slot } from '@/types/models';
import { doctorsApi, ApiError } from '@/lib/api';

interface Props {
    doctors: Doctor[];
    selectedDoctor: Doctor | null;
    onSelectDoctor: (doctor: Doctor | null) => void;
    onBookSlot: (doctor: Doctor, date: string, startTime: string) => void;
    onError: (message: string) => void;
}

function todayStr(): string {
    return new Date().toISOString().split('T')[0];
}

export function AvailabilityPanel({ doctors, selectedDoctor, onSelectDoctor, onBookSlot, onError }: Props) {
    const [date, setDate] = useState(todayStr());
    const [slots, setSlots] = useState<Slot[]>([]);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');

    useEffect(() => {
        if (!selectedDoctor || !date) {
            setSlots([]);
            return;
        }

        let cancelled = false;

        async function fetchSlots() {
            setLoading(true);
            setMessage('');
            setSlots([]);

            try {
                const res = await doctorsApi.availability(selectedDoctor!.id, date);
                if (!cancelled) {
                    setSlots(res.slots);
                    if (res.slots.length === 0) {
                        setMessage('No available slots for this date.');
                    }
                }
            } catch (err) {
                if (!cancelled) {
                    if (err instanceof ApiError) {
                        setMessage(err.message);
                    } else {
                        onError('Failed to load availability');
                    }
                }
            } finally {
                if (!cancelled) setLoading(false);
            }
        }

        fetchSlots();

        return () => {
            cancelled = true;
        };
    }, [selectedDoctor, date, onError]);

    return (
        <div>
            <h2 className="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Doctor Availability</h2>

            <div className="mb-6 flex flex-col gap-4 sm:flex-row">
                {/* Doctor picker */}
                <div className="flex-1">
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Doctor
                    </label>
                    <select
                        value={selectedDoctor?.id ?? ''}
                        onChange={(e) => {
                            const doc = doctors.find((d) => d.id === Number(e.target.value)) ?? null;
                            onSelectDoctor(doc);
                        }}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Select a doctor</option>
                        {doctors.map((d) => (
                            <option key={d.id} value={d.id}>
                                {d.name} — {d.specialization}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Date picker */}
                <div className="w-full sm:w-48">
                    <label className="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date
                    </label>
                    <input
                        type="date"
                        value={date}
                        onChange={(e) => setDate(e.target.value)}
                        min={todayStr()}
                        className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>
            </div>

            {/* Results */}
            {loading && (
                <div className="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Loading slots...
                </div>
            )}

            {!loading && message && (
                <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                    {message}
                </div>
            )}

            {!loading && slots.length > 0 && (
                <div>
                    <p className="mb-3 text-sm text-gray-600 dark:text-gray-400">
                        {slots.length} available slot{slots.length !== 1 ? 's' : ''} on{' '}
                        <span className="font-medium">{date}</span>
                    </p>
                    <div className="grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                        {slots.map((slot) => (
                            <button
                                key={slot.start_time}
                                onClick={() => {
                                    if (selectedDoctor) {
                                        onBookSlot(selectedDoctor, date, slot.start_time);
                                    }
                                }}
                                className="group rounded-lg border border-gray-200 bg-white px-3 py-3 text-center transition-all hover:border-blue-400 hover:bg-blue-50 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-600 dark:hover:bg-blue-900/20"
                            >
                                <span className="block text-sm font-medium text-gray-900 dark:text-white">
                                    {slot.start_time}
                                </span>
                                <span className="block text-xs text-gray-400">to {slot.end_time}</span>
                                <span className="mt-1 block text-xs text-blue-600 opacity-0 transition-opacity group-hover:opacity-100 dark:text-blue-400">
                                    Book →
                                </span>
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {!loading && !message && !selectedDoctor && (
                <div className="rounded-lg border border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
                    <p className="text-gray-500 dark:text-gray-400">
                        Select a doctor and date to view available appointment slots.
                    </p>
                </div>
            )}
        </div>
    );
}
