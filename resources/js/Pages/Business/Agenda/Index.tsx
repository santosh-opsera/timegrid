import StatusBadge from '@/Components/StatusBadge';
import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { Head, Link } from '@inertiajs/react';
import {
    CalendarDaysIcon,
    ClockIcon,
} from '@heroicons/react/24/outline';
import { format, parseISO } from 'date-fns';

interface Appointment {
    id: number;
    code: string;
    status: string;
    start_at: string;
    finish_at: string;
    contact?: { id: number; firstname: string; lastname: string; email: string };
    service?: { id: number; name: string; color?: string };
}

interface Props {
    business: { id: number; name: string; slug: string };
    appointments: Appointment[];
    strategy: string;
    isEmpty: boolean;
}

export default function BusinessAgendaIndex({ business, appointments, isEmpty }: Props) {
    const route = useRoute();

    const grouped = {
        upcoming: appointments.filter((a) => new Date(a.start_at) >= new Date()),
        past: appointments.filter((a) => new Date(a.start_at) < new Date()),
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: business.name, href: route('manager.business.show', { business: business.slug }) as string },
                { label: 'Appointments' },
            ]}
        >
            <Head title={`Appointments — ${business.name}`} />
            <PageHeader
                title="Appointments"
                description="Manage all appointments for your business"
                action={
                    <Link
                        href={route('manager.business.agenda.calendar', { business: business.slug }) as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        <CalendarDaysIcon className="h-5 w-5" aria-hidden="true" />
                        Calendar view
                    </Link>
                }
            />

            {isEmpty ? (
                <EmptyState
                    title="No appointments"
                    description="There are no appointments scheduled for this business yet."
                />
            ) : (
                <div className="space-y-8">
                    {grouped.upcoming.length > 0 && (
                        <section>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                                Upcoming ({grouped.upcoming.length})
                            </h2>
                            <div className="space-y-3">
                                {grouped.upcoming.map((appt) => (
                                    <AppointmentRow key={appt.id} appointment={appt} />
                                ))}
                            </div>
                        </section>
                    )}
                    {grouped.past.length > 0 && (
                        <section>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                                Past ({grouped.past.length})
                            </h2>
                            <div className="space-y-3">
                                {grouped.past.map((appt) => (
                                    <AppointmentRow key={appt.id} appointment={appt} past />
                                ))}
                            </div>
                        </section>
                    )}
                </div>
            )}
        </AuthenticatedLayout>
    );
}

function AppointmentRow({ appointment, past = false }: { appointment: Appointment; past?: boolean }) {
    return (
        <Card className={`transition ${past ? 'opacity-60' : 'hover:border-brand-200 dark:hover:border-brand-800'}`}>
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-start gap-4">
                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-950">
                        <CalendarDaysIcon className="h-6 w-6 text-brand-600 dark:text-brand-400" aria-hidden="true" />
                    </div>
                    <div>
                        <div className="flex items-center gap-3">
                            <h3 className="font-semibold text-slate-900 dark:text-white">
                                {appointment.contact
                                    ? `${appointment.contact.firstname} ${appointment.contact.lastname}`
                                    : 'Unknown contact'}
                            </h3>
                            <StatusBadge status={appointment.status} />
                        </div>
                        <p className="mt-1 text-sm text-slate-500">
                            {appointment.service?.name ?? 'Service'}
                        </p>
                        <div className="mt-2 flex flex-wrap gap-4 text-sm text-slate-500">
                            <span className="flex items-center gap-1">
                                <CalendarDaysIcon className="h-4 w-4" aria-hidden="true" />
                                {format(parseISO(appointment.start_at), 'EEE, MMM d, yyyy')}
                            </span>
                            <span className="flex items-center gap-1">
                                <ClockIcon className="h-4 w-4" aria-hidden="true" />
                                {format(parseISO(appointment.start_at), 'h:mm a')}
                            </span>
                            {appointment.code && (
                                <span className="font-mono text-xs">#{appointment.code}</span>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}
