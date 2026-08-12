import { Card, PageHeader } from '@/Components/UI';
import GuestLayout from '@/Layouts/GuestLayout';
import useRoute from '@/Hooks/useRoute';
import { BusinessShowPageProps } from '@/types/global';
import { Head, Link } from '@inertiajs/react';
import {
    CalendarDaysIcon,
    ClockIcon,
    MapPinIcon,
    PhoneIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import { format, addDays, startOfWeek, eachDayOfInterval, isSameDay } from 'date-fns';
import { useState } from 'react';

export default function PublicBusinessShow({
    business,
    available = true,
}: BusinessShowPageProps) {
    const route = useRoute();
    const [selectedDate, setSelectedDate] = useState(new Date());

    const weekStart = startOfWeek(selectedDate, { weekStartsOn: 1 });
    const weekDays = eachDayOfInterval({ start: weekStart, end: addDays(weekStart, 6) });

    const sampleSlots = ['09:00', '09:30', '10:00', '10:30', '11:00', '14:00', '14:30', '15:00'];

    return (
        <GuestLayout>
            <Head title={business.name} />

            <div className="relative overflow-hidden bg-linear-to-br from-brand-600 via-brand-500 to-blue-500">
                <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggIGQ9Ik0zNiAzNGg0djRoLTR6TTAgMzRoNHY0SDB6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30" />
                <div className="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                    <div className="max-w-2xl">
                        {business.category && (
                            <span className="inline-block rounded-full bg-white/20 px-3 py-1 text-sm font-medium text-white backdrop-blur">
                                {business.category.slug.replace(/-/g, ' ')}
                            </span>
                        )}
                        <h1 className="mt-4 text-4xl font-bold text-white sm:text-5xl">{business.name}</h1>
                        {business.description && (
                            <p className="mt-4 text-lg text-brand-100">{business.description}</p>
                        )}
                        <div className="mt-6 flex flex-wrap gap-4 text-sm text-white/90">
                            {business.postal_address && (
                                <span className="flex items-center gap-2">
                                    <MapPinIcon className="h-5 w-5" aria-hidden="true" />
                                    {business.postal_address}
                                </span>
                            )}
                            {business.phone && (
                                <span className="flex items-center gap-2">
                                    <PhoneIcon className="h-5 w-5" aria-hidden="true" />
                                    {business.phone}
                                </span>
                            )}
                        </div>
                        <Link
                            href={route('user.booking.book', { business: business.slug }) as string}
                            className="mt-8 inline-flex items-center gap-2 rounded-2xl bg-white px-6 py-3 text-base font-semibold text-brand-600 shadow-xl transition hover:bg-brand-50"
                        >
                            <CalendarDaysIcon className="h-5 w-5" aria-hidden="true" />
                            Book appointment
                        </Link>
                    </div>
                </div>
            </div>

            <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <PageHeader title="Services" description="Choose from our available services" />
                        <div className="grid gap-4 sm:grid-cols-2">
                            {(business.services ?? []).map((service) => (
                                <Card key={service.id} className="transition hover:border-brand-200 dark:hover:border-brand-800">
                                    <div className="flex items-start gap-4">
                                        <div
                                            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white"
                                            style={{ backgroundColor: service.color ?? '#4f46e5' }}
                                        >
                                            <WrenchScrewdriverIcon className="h-5 w-5" aria-hidden="true" />
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="font-semibold text-slate-900 dark:text-white">{service.name}</h3>
                                            {service.description && (
                                                <p className="mt-1 text-sm text-slate-500">{service.description}</p>
                                            )}
                                            <div className="mt-2 flex items-center gap-3 text-sm text-slate-500">
                                                {service.duration && (
                                                    <span className="flex items-center gap-1">
                                                        <ClockIcon className="h-4 w-4" aria-hidden="true" />
                                                        {service.duration} min
                                                    </span>
                                                )}
                                                {service.price && (
                                                    <span className="font-semibold text-brand-600">${service.price}</span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </Card>
                            ))}
                        </div>
                    </div>

                    <div>
                        <Card>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Available times</h2>
                            {!available ? (
                                <p className="text-sm text-slate-500">No availability at the moment. Please check back later.</p>
                            ) : (
                                <>
                                    <div className="mb-4 grid grid-cols-7 gap-1">
                                        {weekDays.map((day) => (
                                            <button
                                                key={day.toISOString()}
                                                type="button"
                                                onClick={() => setSelectedDate(day)}
                                                className={`rounded-lg p-2 text-center text-xs transition ${
                                                    isSameDay(day, selectedDate)
                                                        ? 'bg-brand-600 text-white'
                                                        : 'hover:bg-slate-100 dark:hover:bg-slate-800'
                                                }`}
                                                aria-label={format(day, 'EEEE, MMMM d')}
                                                aria-pressed={isSameDay(day, selectedDate)}
                                            >
                                                <div className="font-medium">{format(day, 'EEE')}</div>
                                                <div>{format(day, 'd')}</div>
                                            </button>
                                        ))}
                                    </div>
                                    <div className="grid grid-cols-3 gap-2">
                                        {sampleSlots.map((slot) => (
                                            <Link
                                                key={slot}
                                                href={route('user.booking.book', { business: business.slug, date: format(selectedDate, 'yyyy-MM-dd'), time: slot }) as string}
                                                className="rounded-lg border border-slate-200 py-2 text-center text-sm font-medium transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:hover:border-brand-500 dark:hover:bg-brand-950"
                                            >
                                                {slot}
                                            </Link>
                                        ))}
                                    </div>
                                </>
                            )}
                        </Card>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
