import { Card, PageHeader, StatCard } from '@/Components/UI';
import StatusBadge from '@/Components/StatusBadge';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { BusinessShowPageProps } from '@/types/global';
import { Head, Link } from '@inertiajs/react';
import {
    CalendarDaysIcon,
    CheckCircleIcon,
    ClockIcon,
    MinusCircleIcon,
    UserGroupIcon,
    UsersIcon,
} from '@heroicons/react/24/outline';
import { format, parseISO, isToday } from 'date-fns';

const BOX_ICONS = [
    <CheckCircleIcon className="h-6 w-6" aria-hidden="true" />,
    <MinusCircleIcon className="h-6 w-6" aria-hidden="true" />,
    <ClockIcon className="h-6 w-6" aria-hidden="true" />,
    <UsersIcon className="h-6 w-6" aria-hidden="true" />,
    <UserGroupIcon className="h-6 w-6" aria-hidden="true" />,
    <CalendarDaysIcon className="h-6 w-6" aria-hidden="true" />,
];

const BOX_COLORS: Array<'brand' | 'emerald' | 'amber' | 'blue'> = ['brand', 'emerald', 'amber', 'blue'];

function translateBoxTitle(title: string): string {
    const map: Record<string, string> = {
        'manager.businesses.dashboard.panel.title_appointments_active': 'Active Today',
        'manager.businesses.dashboard.panel.title_appointments_canceled': 'Cancelled Today',
        'manager.businesses.dashboard.panel.title_contacts_subscribed': 'Subscribed',
        'manager.businesses.dashboard.panel.title_contacts_registered': 'Registered',
        'manager.businesses.dashboard.panel.title_appointments_total': 'Total Appointments',
    };
    return map[title] ?? title;
}

export default function ManagerDashboard({
    business,
    boxes = [],
    notifications = [],
    todayAppointments = [],
    time,
}: BusinessShowPageProps) {
    const route = useRoute();

    return (
        <AuthenticatedLayout
            title={business.name}
            breadcrumbs={[
                { label: 'Businesses', href: route('manager.business.index') as string },
                { label: business.name },
            ]}
        >
            <Head title={`${business.name} — Dashboard`} />
            <PageHeader
                title={business.name}
                description={`Manager dashboard · ${time ?? format(new Date(), 'h:mm a')}`}
                action={
                    <Link
                        href={route('manager.business.agenda.calendar', { business: business.slug }) as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        <CalendarDaysIcon className="h-5 w-5" aria-hidden="true" />
                        Calendar
                    </Link>
                }
            />

            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {boxes.length > 0 ? (
                    boxes.map((box: Record<string, unknown>, i: number) => (
                        <StatCard
                            key={i}
                            label={translateBoxTitle((box.title as string) ?? (box.label as string) ?? '')}
                            value={(box.number as number) ?? (box.value as number) ?? 0}
                            icon={BOX_ICONS[i % BOX_ICONS.length]}
                            color={BOX_COLORS[i % BOX_COLORS.length]}
                        />
                    ))
                ) : (
                    <>
                        <StatCard label="Today's appointments" value={0} icon={<CalendarDaysIcon className="h-6 w-6" aria-hidden="true" />} color="brand" />
                        <StatCard label="Total contacts" value={business.contacts?.length ?? 0} icon={<UserGroupIcon className="h-6 w-6" aria-hidden="true" />} color="emerald" />
                        <StatCard label="Services" value={business.services?.length ?? 0} icon={<ClockIcon className="h-6 w-6" aria-hidden="true" />} color="blue" />
                    </>
                )}
            </div>

            <div className="mt-8 grid gap-8 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Today&apos;s agenda</h2>
                        <Link
                            href={route('manager.business.agenda.index', { business: business.slug }) as string}
                            className="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                        >
                            View all
                        </Link>
                    </div>
                    {todayAppointments.length === 0 ? (
                        <p className="py-8 text-center text-sm text-slate-500">No appointments scheduled for today.</p>
                    ) : (
                        <ul className="divide-y divide-slate-100 dark:divide-slate-800" role="list">
                            {todayAppointments.map((appt: Record<string, unknown>) => (
                                <li key={appt.id as number} className="flex items-center justify-between py-4">
                                    <div>
                                        <p className="font-medium text-slate-900 dark:text-white">
                                            {(appt.contact as Record<string, string>)?.firstname ?? ''}{' '}
                                            {(appt.contact as Record<string, string>)?.lastname ?? ''}
                                        </p>
                                        <p className="text-sm text-slate-500">
                                            {(appt.service as Record<string, string>)?.name ?? 'Appointment'}
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <StatusBadge status={(appt.status as string) ?? 'R'} />
                                        <span className="text-sm text-slate-500">
                                            {appt.start_at ? format(parseISO(appt.start_at as string), 'h:mm a') : ''}
                                        </span>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>

                <Card>
                    <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Notifications</h2>
                    {notifications.length === 0 ? (
                        <p className="text-sm text-slate-500">No new notifications.</p>
                    ) : (
                        <ul className="space-y-3" role="list">
                            {notifications.slice(0, 5).map((n) => (
                                <li key={n.id} className="rounded-lg bg-slate-50 p-3 text-sm dark:bg-slate-800/50">
                                    <p className="text-slate-900 dark:text-white">{n.body ?? n.category}</p>
                                    {n.created_at && (
                                        <p className="mt-1 text-xs text-slate-500">
                                            {format(parseISO(n.created_at), 'MMM d, h:mm a')}
                                        </p>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>

            {/* Quick links */}
            <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {[
                    { label: 'Services', href: route('manager.business.service.index', { business: business.slug }) as string },
                    { label: 'Contacts', href: route('manager.addressbook.index', { business: business.slug }) as string },
                    { label: 'Staff', href: route('manager.business.humanresource.index', { business: business.slug }) as string },
                    { label: 'Availability', href: route('manager.business.vacancy.show', { business: business.slug }) as string },
                ].map((link) => (
                    <Link
                        key={link.label}
                        href={link.href}
                        className="rounded-xl border border-slate-200 bg-white p-4 text-center font-medium text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-700 dark:hover:bg-brand-950"
                    >
                        {link.label}
                    </Link>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
