import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Business, PageProps, Service } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';

interface ShowProps {
    business: Business;
}

const STEPS = ['Service', 'Date', 'Time', 'Details'] as const;
type Step = 1 | 2 | 3 | 4;

function formatDate(dateStr: string): string {
    const date = new Date(`${dateStr}T12:00:00`);
    return date.toLocaleDateString(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });
}

function formatTime(timeStr: string): string {
    const [hours, minutes] = timeStr.split(':').map(Number);
    const date = new Date();
    date.setHours(hours, minutes, 0, 0);
    return date.toLocaleTimeString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
    });
}

function splitName(name: string): { firstname: string; lastname: string } {
    const parts = name.trim().split(/\s+/);
    if (parts.length === 0) {
        return { firstname: '', lastname: '' };
    }
    return {
        firstname: parts[0],
        lastname: parts.slice(1).join(' '),
    };
}

export default function Show({ business }: ShowProps) {
    const { auth } = usePage<PageProps>().props;
    const user = auth?.user ?? null;
    const { firstname: defaultFirstname, lastname: defaultLastname } = user?.name
        ? splitName(user.name)
        : { firstname: '', lastname: '' };

    const services = (business.services ?? []).filter((s) => s.is_active);

    const [step, setStep] = useState<Step>(1);
    const [selectedService, setSelectedService] = useState<Service | null>(null);
    const [selectedDate, setSelectedDate] = useState<string | null>(null);
    const [selectedTime, setSelectedTime] = useState<string | null>(null);

    const [dates, setDates] = useState<string[]>([]);
    const [times, setTimes] = useState<string[]>([]);
    const [loadingDates, setLoadingDates] = useState(false);
    const [loadingTimes, setLoadingTimes] = useState(false);
    const [fetchError, setFetchError] = useState<string | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        service_id: '',
        date: '',
        time: '',
        firstname: defaultFirstname,
        lastname: defaultLastname,
        email: user?.email ?? '',
        phone: '',
        comments: '',
    });

    useEffect(() => {
        if (step !== 2 || !selectedService) {
            return;
        }

        setFetchError(null);
        setLoadingDates(true);
        setDates([]);

        fetch(`/api/v1/businesses/${business.slug}/services/${selectedService.id}/dates`)
            .then((res) => {
                if (!res.ok) {
                    throw new Error('Could not load available dates.');
                }
                return res.json();
            })
            .then((body: { dates: string[] }) => {
                setDates(body.dates);
            })
            .catch(() => {
                setFetchError('Unable to load available dates. Please try again.');
            })
            .finally(() => {
                setLoadingDates(false);
            });
    }, [step, selectedService, business.slug]);

    useEffect(() => {
        if (step !== 3 || !selectedService || !selectedDate) {
            return;
        }

        setFetchError(null);
        setLoadingTimes(true);
        setTimes([]);

        fetch(
            `/api/v1/businesses/${business.slug}/services/${selectedService.id}/times/${selectedDate}`,
        )
            .then((res) => {
                if (!res.ok) {
                    throw new Error('Could not load available times.');
                }
                return res.json();
            })
            .then((body: { times: string[] }) => {
                setTimes(body.times);
            })
            .catch(() => {
                setFetchError('Unable to load available times. Please try again.');
            })
            .finally(() => {
                setLoadingTimes(false);
            });
    }, [step, selectedService, selectedDate, business.slug]);

    const selectService = (service: Service) => {
        if (!user) {
            window.location.href = `/login?bookingReturn=${encodeURIComponent(window.location.pathname)}`;
            return;
        }
        setSelectedService(service);
        setSelectedDate(null);
        setSelectedTime(null);
        setData('service_id', String(service.id));
        setData('date', '');
        setData('time', '');
        setStep(2);
    };

    const selectDate = (date: string) => {
        setSelectedDate(date);
        setSelectedTime(null);
        setData('date', date);
        setData('time', '');
        setStep(3);
    };

    const selectTime = (time: string) => {
        setSelectedTime(time);
        setData('time', time);
        setStep(4);
    };

    const goBack = () => {
        setFetchError(null);
        setStep((current) => (current > 1 ? ((current - 1) as Step) : current));
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('booking.store', business.slug));
    };

    return (
        <>
            <Head title={`Book — ${business.name}`} />

            <div className="min-h-screen bg-gradient-to-b from-indigo-50 to-white">
                <header className="border-b border-indigo-100 bg-white/80 backdrop-blur">
                    <div className="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6">
                        <Link href="/" className="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700">
                            <span>←</span>
                            <span>TimeGrid</span>
                        </Link>
                        {user ? (
                            <Link
                                href={route('dashboard')}
                                className="text-sm text-gray-600 hover:text-gray-800"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <Link
                                href={route('login')}
                                className="text-sm text-indigo-600 hover:text-indigo-700"
                            >
                                Log in
                            </Link>
                        )}
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-12">
                    <div className="text-center">
                        <h1 className="text-2xl font-bold text-gray-900 sm:text-3xl">{business.name}</h1>
                        {business.description && (
                            <p className="mt-2 text-sm text-gray-500 sm:text-base">{business.description}</p>
                        )}
                    </div>

                    {/* Stepper */}
                    <nav aria-label="Booking progress" className="mt-8">
                        <ol className="flex items-center justify-between">
                            {STEPS.map((label, index) => {
                                const stepNumber = (index + 1) as Step;
                                const isActive = step === stepNumber;
                                const isComplete = step > stepNumber;

                                return (
                                    <li key={label} className="flex flex-1 flex-col items-center">
                                        <div className="flex w-full items-center">
                                            {index > 0 && (
                                                <div
                                                    className={`h-0.5 flex-1 ${
                                                        isComplete ? 'bg-indigo-600' : 'bg-gray-200'
                                                    }`}
                                                />
                                            )}
                                            <div
                                                className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold transition ${
                                                    isComplete
                                                        ? 'bg-indigo-600 text-white'
                                                        : isActive
                                                          ? 'bg-indigo-600 text-white ring-4 ring-indigo-100'
                                                          : 'bg-gray-200 text-gray-500'
                                                }`}
                                            >
                                                {isComplete ? '✓' : stepNumber}
                                            </div>
                                            {index < STEPS.length - 1 && (
                                                <div
                                                    className={`h-0.5 flex-1 ${
                                                        step > stepNumber ? 'bg-indigo-600' : 'bg-gray-200'
                                                    }`}
                                                />
                                            )}
                                        </div>
                                        <span
                                            className={`mt-2 hidden text-xs font-medium sm:block ${
                                                isActive || isComplete ? 'text-indigo-600' : 'text-gray-400'
                                            }`}
                                        >
                                            {label}
                                        </span>
                                    </li>
                                );
                            })}
                        </ol>
                    </nav>

                    <div className="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        {step > 1 && (
                            <button
                                type="button"
                                onClick={goBack}
                                className="mb-6 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700"
                            >
                                ← Back
                            </button>
                        )}

                        {/* Step 1: Service */}
                        {step === 1 && (
                            <div>
                                <h2 className="text-lg font-semibold text-gray-900">Choose a service</h2>
                                <p className="mt-1 text-sm text-gray-500">Select the service you'd like to book.</p>

                                {services.length === 0 ? (
                                    <div className="mt-6 rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                                        No services available at the moment.
                                    </div>
                                ) : (
                                    <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                        {services.map((service) => (
                                            <button
                                                key={service.id}
                                                type="button"
                                                onClick={() => selectService(service)}
                                                className="group rounded-xl border border-gray-200 p-5 text-left transition hover:border-indigo-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                style={{ borderTopWidth: 4, borderTopColor: service.color }}
                                            >
                                                <h3 className="font-semibold text-gray-900 group-hover:text-indigo-600">
                                                    {service.name}
                                                </h3>
                                                {service.description && (
                                                    <p className="mt-1 line-clamp-2 text-sm text-gray-500">
                                                        {service.description}
                                                    </p>
                                                )}
                                                <p className="mt-3 text-xs font-medium text-indigo-600">
                                                    {service.duration} min
                                                </p>
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Step 2: Date */}
                        {step === 2 && selectedService && (
                            <div>
                                <h2 className="text-lg font-semibold text-gray-900">Choose a date</h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    Booking <span className="font-medium text-gray-700">{selectedService.name}</span>
                                </p>

                                {loadingDates && (
                                    <div className="mt-8 flex items-center justify-center gap-3 text-sm text-gray-500">
                                        <span className="h-5 w-5 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
                                        Loading available dates…
                                    </div>
                                )}

                                {fetchError && !loadingDates && (
                                    <div className="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {fetchError}
                                    </div>
                                )}

                                {!loadingDates && !fetchError && dates.length === 0 && (
                                    <div className="mt-6 rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                                        No available dates right now. Please check back later.
                                    </div>
                                )}

                                {!loadingDates && dates.length > 0 && (
                                    <div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                        {dates.map((date) => (
                                            <button
                                                key={date}
                                                type="button"
                                                onClick={() => selectDate(date)}
                                                className={`rounded-xl border px-4 py-3 text-sm font-medium transition hover:border-indigo-400 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 ${
                                                    selectedDate === date
                                                        ? 'border-indigo-600 bg-indigo-50 text-indigo-700'
                                                        : 'border-gray-200 text-gray-700'
                                                }`}
                                            >
                                                {formatDate(date)}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Step 3: Time */}
                        {step === 3 && selectedService && selectedDate && (
                            <div>
                                <h2 className="text-lg font-semibold text-gray-900">Choose a time</h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    {selectedService.name} on{' '}
                                    <span className="font-medium text-gray-700">{formatDate(selectedDate)}</span>
                                </p>

                                {loadingTimes && (
                                    <div className="mt-8 flex items-center justify-center gap-3 text-sm text-gray-500">
                                        <span className="h-5 w-5 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent" />
                                        Loading available times…
                                    </div>
                                )}

                                {fetchError && !loadingTimes && (
                                    <div className="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {fetchError}
                                    </div>
                                )}

                                {!loadingTimes && !fetchError && times.length === 0 && (
                                    <div className="mt-6 rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                                        No available times for this date. Try another date.
                                    </div>
                                )}

                                {!loadingTimes && times.length > 0 && (
                                    <div className="mt-6 grid grid-cols-3 gap-3 sm:grid-cols-4">
                                        {times.map((time) => (
                                            <button
                                                key={time}
                                                type="button"
                                                onClick={() => selectTime(time)}
                                                className={`rounded-lg border px-3 py-2.5 text-sm font-medium transition hover:border-indigo-400 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 ${
                                                    selectedTime === time
                                                        ? 'border-indigo-600 bg-indigo-600 text-white'
                                                        : 'border-gray-200 text-gray-700'
                                                }`}
                                            >
                                                {formatTime(time)}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Step 4: Details */}
                        {step === 4 && selectedService && selectedDate && selectedTime && (
                            <div>
                                <h2 className="text-lg font-semibold text-gray-900">Your details</h2>
                                <p className="mt-1 text-sm text-gray-500">
                                    Almost done! Confirm your booking details below.
                                </p>

                                <div className="mt-4 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                                    <p className="font-medium">{selectedService.name}</p>
                                    <p className="mt-0.5 text-indigo-700">
                                        {formatDate(selectedDate)} at {formatTime(selectedTime)} ·{' '}
                                        {selectedService.duration} min
                                    </p>
                                </div>

                                {(errors.time || errors.date || errors.service_id) && (
                                    <div className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {errors.time || errors.date || errors.service_id}
                                    </div>
                                )}

                                <form onSubmit={submit} className="mt-6 space-y-4">
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <InputLabel htmlFor="firstname" value="First name" />
                                            <TextInput
                                                id="firstname"
                                                value={data.firstname}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('firstname', e.target.value)}
                                                required
                                            />
                                            <InputError message={errors.firstname} className="mt-2" />
                                        </div>
                                        <div>
                                            <InputLabel htmlFor="lastname" value="Last name" />
                                            <TextInput
                                                id="lastname"
                                                value={data.lastname}
                                                className="mt-1 block w-full"
                                                onChange={(e) => setData('lastname', e.target.value)}
                                            />
                                            <InputError message={errors.lastname} className="mt-2" />
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="email" value="Email" />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            className="mt-1 block w-full"
                                            onChange={(e) => setData('email', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.email} className="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="phone" value="Phone (optional)" />
                                        <TextInput
                                            id="phone"
                                            type="tel"
                                            value={data.phone}
                                            className="mt-1 block w-full"
                                            onChange={(e) => setData('phone', e.target.value)}
                                        />
                                        <InputError message={errors.phone} className="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="comments" value="Comments (optional)" />
                                        <textarea
                                            id="comments"
                                            value={data.comments}
                                            rows={3}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            onChange={(e) => setData('comments', e.target.value)}
                                        />
                                        <InputError message={errors.comments} className="mt-2" />
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="mt-2 w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                    >
                                        {processing ? 'Confirming…' : 'Confirm Booking'}
                                    </button>
                                </form>
                            </div>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
