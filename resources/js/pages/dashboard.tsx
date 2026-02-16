import { useState, useEffect, useCallback } from 'react';
import { Head } from '@inertiajs/react';
import { DoctorList } from './dashboard/DoctorList';
import { DoctorForm } from './dashboard/DoctorForm';
import { AvailabilityPanel } from './dashboard/AvailabilityPanel';
import { BookingForm } from './dashboard/BookingForm';
import type { Doctor } from '@/types/models';
import { doctorsApi } from '@/lib/api';

type Tab = 'doctors' | 'availability' | 'booking';

export default function Dashboard() {
    const [activeTab, setActiveTab] = useState<Tab>('doctors');
    const [doctors, setDoctors] = useState<Doctor[]>([]);
    const [editingDoctor, setEditingDoctor] = useState<Doctor | null>(null);
    const [showDoctorForm, setShowDoctorForm] = useState(false);
    const [selectedDoctor, setSelectedDoctor] = useState<Doctor | null>(null);
    const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

    const loadDoctors = useCallback(async () => {
        try {
            const res = await doctorsApi.list();
            setDoctors(res.data);
        } catch {
            showToast('Failed to load doctors', 'error');
        }
    }, []);

    useEffect(() => {
        loadDoctors();
    }, [loadDoctors]);

    function showToast(message: string, type: 'success' | 'error' = 'success') {
        setToast({ message, type });
        setTimeout(() => setToast(null), 4000);
    }

    function handleEditDoctor(doctor: Doctor) {
        setEditingDoctor(doctor);
        setShowDoctorForm(true);
    }

    function handleDoctorSaved() {
        setShowDoctorForm(false);
        setEditingDoctor(null);
        loadDoctors();
        showToast(editingDoctor ? 'Doctor updated' : 'Doctor created');
    }

    function handleCheckAvailability(doctor: Doctor) {
        setSelectedDoctor(doctor);
        setActiveTab('availability');
    }

    function handleBookSlot(doctor: Doctor, date: string, startTime: string) {
        setSelectedDoctor(doctor);
        setActiveTab('booking');
    }

    const tabs: { key: Tab; label: string }[] = [
        { key: 'doctors', label: 'Doctors' },
        { key: 'availability', label: 'Availability' },
        { key: 'booking', label: 'Book Appointment' },
    ];

    return (
        <>
            <Head title="Dashboard" />
            <div className="min-h-screen bg-gray-50 dark:bg-gray-950">
                {/* Header */}
                <header className="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6">
                        <h1 className="text-xl font-semibold text-gray-900 dark:text-white">
                            Medical Appointments
                        </h1>
                    </div>
                </header>

                {/* Tabs */}
                <div className="mx-auto max-w-7xl px-4 sm:px-6">
                    <nav className="flex gap-1 border-b border-gray-200 pt-4 dark:border-gray-800">
                        {tabs.map((tab) => (
                            <button
                                key={tab.key}
                                onClick={() => setActiveTab(tab.key)}
                                className={`rounded-t-lg px-4 py-2.5 text-sm font-medium transition-colors ${
                                    activeTab === tab.key
                                        ? 'border-b-2 border-blue-600 bg-white text-blue-600 dark:bg-gray-900 dark:text-blue-400'
                                        : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </nav>
                </div>

                {/* Content */}
                <main className="mx-auto max-w-7xl px-4 py-6 sm:px-6">
                    {activeTab === 'doctors' && (
                        <>
                            {showDoctorForm ? (
                                <DoctorForm
                                    doctor={editingDoctor}
                                    onSaved={handleDoctorSaved}
                                    onCancel={() => {
                                        setShowDoctorForm(false);
                                        setEditingDoctor(null);
                                    }}
                                    onError={(msg) => showToast(msg, 'error')}
                                />
                            ) : (
                                <DoctorList
                                    doctors={doctors}
                                    onAdd={() => setShowDoctorForm(true)}
                                    onEdit={handleEditDoctor}
                                    onDelete={async (doctor) => {
                                        if (!confirm(`Delete Dr. ${doctor.name}?`)) return;
                                        try {
                                            await doctorsApi.delete(doctor.id);
                                            loadDoctors();
                                            showToast('Doctor deleted');
                                        } catch {
                                            showToast('Failed to delete doctor', 'error');
                                        }
                                    }}
                                    onCheckAvailability={handleCheckAvailability}
                                />
                            )}
                        </>
                    )}

                    {activeTab === 'availability' && (
                        <AvailabilityPanel
                            doctors={doctors}
                            selectedDoctor={selectedDoctor}
                            onSelectDoctor={setSelectedDoctor}
                            onBookSlot={handleBookSlot}
                            onError={(msg) => showToast(msg, 'error')}
                        />
                    )}

                    {activeTab === 'booking' && (
                        <BookingForm
                            doctors={doctors}
                            selectedDoctor={selectedDoctor}
                            onBooked={(msg) => {
                                showToast(msg);
                                setActiveTab('doctors');
                            }}
                            onError={(msg) => showToast(msg, 'error')}
                        />
                    )}
                </main>

                {/* Toast */}
                {toast && (
                    <div
                        className={`fixed right-4 bottom-4 z-50 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg transition-all ${
                            toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'
                        }`}
                    >
                        {toast.message}
                    </div>
                )}
            </div>
        </>
    );
}
