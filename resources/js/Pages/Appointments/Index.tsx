import StatusBadge from '@/Components/StatusBadge';
import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { AppointmentsIndexPageProps } from '@/types/global';
import { Head, Link } from '@inertiajs/react';
import { CalendarDaysIcon, ClockIcon } from '@heroicons/react/24/outline';
import { format, parseISO } from 'date-fns';

export default function AppointmentsIndex({ appointments }: AppointmentsIndexPageProps) {
    const route = useRoute();

    const grouped = {
        upcoming: appointments.filter((a) => new Date(a.start_at) >= new Date()),
        past: appointments.filter((a) => new Date(a.start_at) < new Date()),
    };

    return (
        <AuthenticatedLayout
            title="My Appointments"
            breadcrumbs={[{ label: 'My Appointments' }]}
        >
            <Head title="My Appointments" />
            <PageHeader
                title="My Appointments"
                description="View and manage your scheduled appointments"
                action={
                    <Link
                        href={route('user.directory.list') as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        Book new appointment
                    </Link>
                }
            />

            {appointments.length === 0 ? (
                <EmptyState
                    title="No appointments yet"
                    description="Browse the directory to book your first appointment."
                    action={
                        <Link href={route('user.directory.list') as string} className="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
                            Browse directory
                        </Link>
                    }
                />
            ) : (
                <div className="space-y-8">
                    {grouped.upcoming.length > 0 && (
                        <section>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Upcoming</h2>
                            <div className="space-y-4">
                                {grouped.upcoming.map((appointment) => (
                                    <AppointmentCard key={appointment.id} appointment={appointment} />
                                ))}
                            </div>
                        </section>
                    )}
                    {grouped.past.length > 0 && (
                        <section>
                            <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Past</h2>
                            <div className="space-y-4">
                                {grouped.past.map((appointment) => (
                                    <AppointmentCard key={appointment.id} appointment={appointment} past />
                                ))}
                            </div>
                        </section>
                    )}
                </div>
            )}
        </AuthenticatedLayout>
    );
}

function AppointmentCard({
    appointment,
    past = false,
}: {
    appointment: AppointmentsIndexPageProps['appointments'][0];
    past?: boolean;
}) {
    return (
        <Card className={`transition ${past ? 'opacity-75' : 'hover:border-brand-200 dark:hover:border-brand-800'}`}>
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-start gap-4">
                    <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-950">
                        <CalendarDaysIcon className="h-6 w-6 text-brand-600 dark:text-brand-400" aria-hidden="true" />
                    </div>
                    <div>
                        <div className="flex items-center gap-3">
                            <h3 className="font-semibold text-slate-900 dark:text-white">
                                {appointment.service?.name ?? 'Appointment'}
                            </h3>
                            <StatusBadge status={appointment.status} />
                        </div>
                        <p className="mt-1 text-sm text-slate-500">{appointment.business?.name}</p>
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
