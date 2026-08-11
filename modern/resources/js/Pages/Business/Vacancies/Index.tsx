import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Business, Service, Staff, Vacancy } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Props {
    business: Business;
    vacancies: Vacancy[];
    services: Service[];
    staff: Staff[];
}

function formatTime(time: string): string {
    return time.slice(0, 5);
}

function next14Days(): { date: string; label: string }[] {
    const days = [];
    const today = new Date();
    for (let i = 0; i < 14; i++) {
        const d = new Date(today);
        d.setDate(today.getDate() + i);
        days.push({
            date: d.toISOString().slice(0, 10),
            label: d.toLocaleDateString(undefined, {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
            }),
        });
    }
    return days;
}

export default function Index({ business, vacancies, services, staff }: Props) {
    const [selectedDates, setSelectedDates] = useState<string[]>([]);

    const createForm = useForm({
        service_id: services[0]?.id ?? '',
        staff_id: '',
        date: '',
        start_time: '09:00',
        end_time: '17:00',
    });

    const bulkForm = useForm({
        service_id: services[0]?.id ?? '',
        staff_id: '',
        dates: [] as string[],
        start_time: '09:00',
        end_time: '17:00',
    });

    const grouped = vacancies.reduce<Record<string, Vacancy[]>>((acc, vacancy) => {
        const dateKey = vacancy.date.slice(0, 10);
        if (!acc[dateKey]) acc[dateKey] = [];
        acc[dateKey].push(vacancy);
        return acc;
    }, {});

    const submitCreate: FormEventHandler = (e) => {
        e.preventDefault();
        createForm.post(route('businesses.vacancies.store', business.slug), {
            onSuccess: () => createForm.reset('date'),
        });
    };

    const submitBulk: FormEventHandler = (e) => {
        e.preventDefault();
        router.post(route('businesses.vacancies.bulk', business.slug), {
            service_id: bulkForm.data.service_id,
            staff_id: bulkForm.data.staff_id || null,
            dates: selectedDates,
            start_time: bulkForm.data.start_time,
            end_time: bulkForm.data.end_time,
        }, {
            onSuccess: () => setSelectedDates([]),
        });
    };

    const toggleDate = (date: string) => {
        setSelectedDates((prev) =>
            prev.includes(date) ? prev.filter((d) => d !== date) : [...prev, date],
        );
    };

    const deleteVacancy = (vacancy: Vacancy) => {
        if (confirm('Remove this vacancy?')) {
            router.delete(route('businesses.vacancies.destroy', [business.slug, vacancy.id]));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Vacancies — {business.name}
                    </h2>
                    <Link
                        href={route('businesses.show', business.slug)}
                        className="text-sm text-gray-600 hover:text-gray-800"
                    >
                        ← Back to Dashboard
                    </Link>
                </div>
            }
        >
            <Head title={`Vacancies — ${business.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl space-y-8 sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="mb-4 text-base font-semibold text-gray-900">Add Vacancy</h3>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel htmlFor="service_id" value="Service" />
                                <select
                                    id="service_id"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={createForm.data.service_id}
                                    onChange={(e) =>
                                        createForm.setData('service_id', parseInt(e.target.value))
                                    }
                                    required
                                >
                                    {services.map((service) => (
                                        <option key={service.id} value={service.id}>
                                            {service.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={createForm.errors.service_id} />
                            </div>
                            <div>
                                <InputLabel htmlFor="staff_id" value="Staff (optional)" />
                                <select
                                    id="staff_id"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={createForm.data.staff_id}
                                    onChange={(e) => createForm.setData('staff_id', e.target.value)}
                                >
                                    <option value="">Any staff</option>
                                    {staff.map((member) => (
                                        <option key={member.id} value={member.id}>
                                            {member.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={createForm.errors.staff_id} />
                            </div>
                            <div>
                                <InputLabel htmlFor="date" value="Date" />
                                <input
                                    id="date"
                                    type="date"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={createForm.data.date}
                                    onChange={(e) => createForm.setData('date', e.target.value)}
                                    required
                                />
                                <InputError className="mt-2" message={createForm.errors.date} />
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <InputLabel htmlFor="start_time" value="Start Time" />
                                    <input
                                        id="start_time"
                                        type="time"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={createForm.data.start_time}
                                        onChange={(e) =>
                                            createForm.setData('start_time', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError className="mt-2" message={createForm.errors.start_time} />
                                </div>
                                <div>
                                    <InputLabel htmlFor="end_time" value="End Time" />
                                    <input
                                        id="end_time"
                                        type="time"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={createForm.data.end_time}
                                        onChange={(e) =>
                                            createForm.setData('end_time', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError className="mt-2" message={createForm.errors.end_time} />
                                </div>
                            </div>
                        </div>
                        <div className="mt-4">
                            <PrimaryButton disabled={createForm.processing}>Add Vacancy</PrimaryButton>
                        </div>
                    </form>

                    <form
                        onSubmit={submitBulk}
                        className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="mb-4 text-base font-semibold text-gray-900">Bulk Create</h3>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Service" />
                                <select
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={bulkForm.data.service_id}
                                    onChange={(e) =>
                                        bulkForm.setData('service_id', parseInt(e.target.value))
                                    }
                                    required
                                >
                                    {services.map((service) => (
                                        <option key={service.id} value={service.id}>
                                            {service.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Staff (optional)" />
                                <select
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={bulkForm.data.staff_id}
                                    onChange={(e) => bulkForm.setData('staff_id', e.target.value)}
                                >
                                    <option value="">Any staff</option>
                                    {staff.map((member) => (
                                        <option key={member.id} value={member.id}>
                                            {member.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Start Time" />
                                <input
                                    type="time"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={bulkForm.data.start_time}
                                    onChange={(e) => bulkForm.setData('start_time', e.target.value)}
                                    required
                                />
                            </div>
                            <div>
                                <InputLabel value="End Time" />
                                <input
                                    type="time"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value={bulkForm.data.end_time}
                                    onChange={(e) => bulkForm.setData('end_time', e.target.value)}
                                    required
                                />
                            </div>
                        </div>

                        <div className="mt-4">
                            <InputLabel value="Select Dates (next 14 days)" />
                            <div className="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                {next14Days().map((day) => (
                                    <label
                                        key={day.date}
                                        className={`flex cursor-pointer items-center gap-2 rounded-md border p-2 text-sm ${
                                            selectedDates.includes(day.date)
                                                ? 'border-indigo-500 bg-indigo-50'
                                                : 'border-gray-200 hover:bg-gray-50'
                                        }`}
                                    >
                                        <input
                                            type="checkbox"
                                            checked={selectedDates.includes(day.date)}
                                            onChange={() => toggleDate(day.date)}
                                            className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        {day.label}
                                    </label>
                                ))}
                            </div>
                            <InputError className="mt-2" message={bulkForm.errors.dates} />
                        </div>

                        <div className="mt-4">
                            <PrimaryButton disabled={bulkForm.processing || selectedDates.length === 0}>
                                Create {selectedDates.length} Vacanc{selectedDates.length === 1 ? 'y' : 'ies'}
                            </PrimaryButton>
                        </div>
                    </form>

                    <section>
                        <h3 className="mb-4 text-base font-semibold text-gray-900">
                            Upcoming Vacancies
                        </h3>

                        {Object.keys(grouped).length === 0 ? (
                            <div className="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                                No upcoming vacancies.
                            </div>
                        ) : (
                            <div className="space-y-6">
                                {Object.entries(grouped).map(([date, dayVacancies]) => (
                                    <div
                                        key={date}
                                        className="rounded-lg border border-gray-200 bg-white shadow-sm"
                                    >
                                        <div className="border-b border-gray-200 bg-gray-50 px-4 py-3">
                                            <h4 className="font-medium text-gray-900">
                                                {new Date(date + 'T00:00:00').toLocaleDateString(undefined, {
                                                    weekday: 'long',
                                                    month: 'long',
                                                    day: 'numeric',
                                                })}
                                            </h4>
                                        </div>
                                        <ul className="divide-y divide-gray-200">
                                            {dayVacancies.map((vacancy) => (
                                                <li
                                                    key={vacancy.id}
                                                    className="flex items-center justify-between px-4 py-3"
                                                >
                                                    <div>
                                                        <span className="font-medium text-gray-900">
                                                            {vacancy.service?.name ?? 'Service'}
                                                        </span>
                                                        <span className="mx-2 text-gray-400">·</span>
                                                        <span className="text-sm text-gray-600">
                                                            {formatTime(vacancy.start_time)} –{' '}
                                                            {formatTime(vacancy.end_time)}
                                                        </span>
                                                        {vacancy.staff && (
                                                            <span className="ml-2 text-sm text-gray-500">
                                                                ({vacancy.staff.name})
                                                            </span>
                                                        )}
                                                    </div>
                                                    <button
                                                        onClick={() => deleteVacancy(vacancy)}
                                                        className="text-sm text-red-600 hover:text-red-800"
                                                    >
                                                        Delete
                                                    </button>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                        )}
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
