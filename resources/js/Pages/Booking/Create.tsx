import { Card } from '@/Components/UI';
import GuestLayout from '@/Layouts/GuestLayout';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { BookingCreatePageProps } from '@/types/global';
import { Head, useForm, usePage } from '@inertiajs/react';
import {
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import { format, parseISO } from 'date-fns';
import { FormEvent, useState } from 'react';
import { Service } from '@/types';

const steps = [
    { id: 1, name: 'Service', icon: WrenchScrewdriverIcon },
    { id: 2, name: 'Date', icon: CalendarDaysIcon },
    { id: 3, name: 'Time', icon: ClockIcon },
    { id: 4, name: 'Confirm', icon: CheckCircleIcon },
];

export default function BookingCreate({
    business,
    availability,
    startFromDate,
    contact,
}: BookingCreatePageProps) {
    const route = useRoute();
    const { auth } = usePage().props;
    const Layout = auth.user ? AuthenticatedLayout : GuestLayout;

    const [step, setStep] = useState(1);
    const [selectedService, setSelectedService] = useState<Service | null>(null);
    const [selectedDate, setSelectedDate] = useState(startFromDate);
    const [selectedTime, setSelectedTime] = useState('');

    const availableDates = Object.keys(availability ?? {});
    const availableTimes = (availability?.[selectedDate] as string[]) ?? [];

    const { data, setData, post, processing, errors } = useForm({
        businessId: business.id,
        service_id: 0,
        _date: startFromDate,
        _time: '',
        _timezone: business.timezone ?? 'UTC',
        contact_id: contact?.id ?? null,
        comments: '',
    });

    const goNext = () => setStep((s) => Math.min(s + 1, 4));
    const goBack = () => setStep((s) => Math.max(s - 1, 1));

    const selectService = (service: Service) => {
        setSelectedService(service);
        setData('service_id', service.id);
        goNext();
    };

    const selectDate = (date: string) => {
        setSelectedDate(date);
        setData('_date', date);
        setSelectedTime('');
        goNext();
    };

    const selectTime = (time: string) => {
        setSelectedTime(time);
        setData('_time', time);
        goNext();
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('user.booking.store', { business: business.slug }) as string);
    };

    return (
        <Layout breadcrumbs={[{ label: 'Directory', href: route('user.directory.list') as string }, { label: business.name }, { label: 'Book' }]}>
            <Head title={`Book — ${business.name}`} />

            <div className="mx-auto max-w-3xl">
                <div className="mb-8 text-center">
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Book with {business.name}</h1>
                    <p className="mt-1 text-slate-500">Complete the steps below to schedule your appointment</p>
                </div>

                {/* Step indicator */}
                <nav aria-label="Booking progress" className="mb-10">
                    <ol className="flex items-center justify-between">
                        {steps.map((s, index) => (
                            <li key={s.id} className="flex flex-1 items-center">
                                <div className="flex flex-col items-center">
                                    <div
                                        className={`flex h-10 w-10 items-center justify-center rounded-full transition ${
                                            step >= s.id
                                                ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30'
                                                : 'bg-slate-100 text-slate-400 dark:bg-slate-800'
                                        }`}
                                    >
                                        <s.icon className="h-5 w-5" aria-hidden="true" />
                                    </div>
                                    <span className={`mt-2 text-xs font-medium ${step >= s.id ? 'text-brand-600' : 'text-slate-400'}`}>
                                        {s.name}
                                    </span>
                                </div>
                                {index < steps.length - 1 && (
                                    <div className={`mx-2 h-0.5 flex-1 ${step > s.id ? 'bg-brand-600' : 'bg-slate-200 dark:bg-slate-700'}`} />
                                )}
                            </li>
                        ))}
                    </ol>
                </nav>

                <Card>
                    {step === 1 && (
                        <div>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Select a service</h2>
                            <div className="space-y-3">
                                {(business.services ?? []).map((service) => (
                                    <button
                                        key={service.id}
                                        type="button"
                                        onClick={() => selectService(service)}
                                        className="flex w-full items-center gap-4 rounded-xl border border-slate-200 p-4 text-left transition hover:border-brand-500 hover:bg-brand-50 dark:border-slate-700 dark:hover:border-brand-500 dark:hover:bg-brand-950"
                                    >
                                        <div
                                            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white"
                                            style={{ backgroundColor: service.color ?? '#4f46e5' }}
                                        >
                                            <WrenchScrewdriverIcon className="h-5 w-5" aria-hidden="true" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="font-medium text-slate-900 dark:text-white">{service.name}</p>
                                            <p className="text-sm text-slate-500">
                                                {service.duration} min{service.price ? ` · $${service.price}` : ''}
                                            </p>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {step === 2 && (
                        <div>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Select a date</h2>
                            <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
                                {availableDates.map((date) => (
                                    <button
                                        key={date}
                                        type="button"
                                        onClick={() => selectDate(date)}
                                        className={`rounded-xl border p-3 text-center transition ${
                                            selectedDate === date
                                                ? 'border-brand-600 bg-brand-50 dark:bg-brand-950'
                                                : 'border-slate-200 hover:border-brand-300 dark:border-slate-700'
                                        }`}
                                    >
                                        <div className="text-xs text-slate-500">{format(parseISO(date), 'EEE')}</div>
                                        <div className="text-lg font-semibold text-slate-900 dark:text-white">{format(parseISO(date), 'd')}</div>
                                        <div className="text-xs text-slate-500">{format(parseISO(date), 'MMM')}</div>
                                    </button>
                                ))}
                            </div>
                            <button type="button" onClick={goBack} className="mt-4 text-sm font-medium text-brand-600 hover:text-brand-700">
                                ← Back
                            </button>
                        </div>
                    )}

                    {step === 3 && (
                        <div>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Select a time</h2>
                            <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
                                {availableTimes.map((time) => (
                                    <button
                                        key={time}
                                        type="button"
                                        onClick={() => selectTime(time)}
                                        className={`rounded-xl border py-3 text-sm font-medium transition ${
                                            selectedTime === time
                                                ? 'border-brand-600 bg-brand-600 text-white'
                                                : 'border-slate-200 hover:border-brand-500 dark:border-slate-700'
                                        }`}
                                    >
                                        {time}
                                    </button>
                                ))}
                            </div>
                            {availableTimes.length === 0 && (
                                <p className="text-sm text-slate-500">No times available for this date. Please select another date.</p>
                            )}
                            <button type="button" onClick={goBack} className="mt-4 text-sm font-medium text-brand-600 hover:text-brand-700">
                                ← Back
                            </button>
                        </div>
                    )}

                    {step === 4 && selectedService && (
                        <form onSubmit={submit}>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Confirm your booking</h2>
                            <dl className="space-y-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                                <div className="flex justify-between">
                                    <dt className="text-sm text-slate-500">Service</dt>
                                    <dd className="text-sm font-medium text-slate-900 dark:text-white">{selectedService.name}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-sm text-slate-500">Date</dt>
                                    <dd className="text-sm font-medium text-slate-900 dark:text-white">
                                        {format(parseISO(selectedDate), 'EEEE, MMMM d, yyyy')}
                                    </dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-sm text-slate-500">Time</dt>
                                    <dd className="text-sm font-medium text-slate-900 dark:text-white">{selectedTime}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-sm text-slate-500">Duration</dt>
                                    <dd className="text-sm font-medium text-slate-900 dark:text-white">{selectedService.duration} min</dd>
                                </div>
                            </dl>

                            <div className="mt-4">
                                <label htmlFor="comments" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Comments (optional)
                                </label>
                                <textarea
                                    id="comments"
                                    value={data.comments}
                                    onChange={(e) => setData('comments', e.target.value)}
                                    rows={3}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>

                            {errors.service_id && <p className="mt-2 text-sm text-red-600" role="alert">{errors.service_id}</p>}

                            <div className="mt-6 flex gap-3">
                                <button type="button" onClick={goBack} className="flex-1 rounded-xl border border-slate-300 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                                    Back
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="flex-1 rounded-xl bg-brand-600 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 disabled:opacity-50"
                                >
                                    {processing ? 'Booking…' : 'Confirm booking'}
                                </button>
                            </div>
                        </form>
                    )}
                </Card>
            </div>
        </Layout>
    );
}
