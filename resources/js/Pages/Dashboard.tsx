import { Card, EmptyState, PageHeader, StatCard } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { DashboardPageProps } from '@/types/global';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowTrendingUpIcon,
    CalendarDaysIcon,
    ClockIcon,
    UserGroupIcon,
} from '@heroicons/react/24/outline';
import { format, isToday, parseISO } from 'date-fns';

export default function Dashboard({
    appointments = [],
    appointmentsCount = 0,
    subscriptionsCount = 0,
}: DashboardPageProps) {
    const route = useRoute();

    const todayCount = appointments.filter((a) => isToday(parseISO(a.start_at))).length;
    const upcoming = appointments
        .filter((a) => new Date(a.start_at) >= new Date())
        .slice(0, 5);

    const recentActivity = appointments.slice(0, 4).map((a) => ({
        id: a.id,
        description: `Appointment with ${a.business?.name ?? 'Business'} — ${a.service?.name ?? 'Service'}`,
        time: format(parseISO(a.start_at), 'MMM d, h:mm a'),
        status: a.status,
    }));

    return (
        <AuthenticatedLayout
            title="Dashboard"
            breadcrumbs={[{ label: 'Dashboard' }]}
        >
            <Head title="Dashboard" />
            <PageHeader
                title="Dashboard"
                description="Overview of your appointments and activity"
                action={
                    <Link
                        href={route('user.directory.list') as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        Book appointment
                    </Link>
                }
            />

            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    label="Today's appointments"
                    value={todayCount}
                    icon={<CalendarDaysIcon className="h-6 w-6" aria-hidden="true" />}
                    color="brand"
                />
                <StatCard
                    label="Total appointments"
                    value={appointmentsCount}
                    icon={<ClockIcon className="h-6 w-6" aria-hidden="true" />}
                    color="blue"
                />
                <StatCard
                    label="Subscriptions"
                    value={subscriptionsCount}
                    icon={<UserGroupIcon className="h-6 w-6" aria-hidden="true" />}
                    color="emerald"
                />
                <StatCard
                    label="Upcoming"
                    value={upcoming.length}
                    icon={<ArrowTrendingUpIcon className="h-6 w-6" aria-hidden="true" />}
                    color="amber"
                />
            </div>

            <div className="mt-8 grid gap-8 lg:grid-cols-2">
                <Card>
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Upcoming appointments</h2>
                        <Link href={route('user.agenda') as string} className="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                            View all
                        </Link>
                    </div>
                    {upcoming.length === 0 ? (
                        <EmptyState
                            title="No upcoming appointments"
                            description="Browse the directory to book your next appointment."
                            action={
                                <Link href={route('user.directory.list') as string} className="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
                                    Browse directory
                                </Link>
                            }
                        />
                    ) : (
                        <ul className="divide-y divide-slate-100 dark:divide-slate-800" role="list">
                            {upcoming.map((appointment) => (
                                <li key={appointment.id} className="flex items-center justify-between py-4">
                                    <div>
                                        <p className="font-medium text-slate-900 dark:text-white">
                                            {appointment.service?.name ?? 'Appointment'}
                                        </p>
                                        <p className="text-sm text-slate-500">
                                            {appointment.business?.name} · {format(parseISO(appointment.start_at), 'EEE, MMM d · h:mm a')}
                                        </p>
                                    </div>
                                    <span className="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-950 dark:text-brand-300">
                                        {appointment.status}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>

                <Card>
                    <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Recent activity</h2>
                    {recentActivity.length === 0 ? (
                        <p className="text-sm text-slate-500">No recent activity to show.</p>
                    ) : (
                        <ul className="space-y-4" role="list">
                            {recentActivity.map((item) => (
                                <li key={item.id} className="flex gap-4">
                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-950">
                                        <CalendarDaysIcon className="h-5 w-5 text-brand-600 dark:text-brand-400" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-slate-900 dark:text-white">{item.description}</p>
                                        <p className="text-xs text-slate-500">{item.time}</p>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
