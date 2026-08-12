import { Card, PageHeader, StatCard } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { BusinessShowPageProps } from '@/types/global';
import { Head, Link } from '@inertiajs/react';
import {
    CalendarDaysIcon,
    ChartBarIcon,
    ClockIcon,
    CurrencyDollarIcon,
    UserGroupIcon,
} from '@heroicons/react/24/outline';
import { format, parseISO } from 'date-fns';

export default function ManagerDashboard({
    business,
    boxes = [],
    notifications = [],
    time,
}: BusinessShowPageProps) {
    const route = useRoute();

    const todayAgenda = (business.contacts ?? []).slice(0, 5);

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

            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {boxes.length > 0 ? (
                    boxes.map((box, i) => (
                        <StatCard
                            key={box.label}
                            label={box.label}
                            value={box.value}
                            icon={<ChartBarIcon className="h-6 w-6" aria-hidden="true" />}
                            color={(['brand', 'emerald', 'amber', 'blue'] as const)[i % 4]}
                        />
                    ))
                ) : (
                    <>
                        <StatCard label="Today's appointments" value={0} icon={<CalendarDaysIcon className="h-6 w-6" aria-hidden="true" />} color="brand" />
                        <StatCard label="Total contacts" value={business.contacts?.length ?? 0} icon={<UserGroupIcon className="h-6 w-6" aria-hidden="true" />} color="emerald" />
                        <StatCard label="Services" value={business.services?.length ?? 0} icon={<ClockIcon className="h-6 w-6" aria-hidden="true" />} color="blue" />
                        <StatCard label="Revenue" value="$—" icon={<CurrencyDollarIcon className="h-6 w-6" aria-hidden="true" />} color="amber" trend="Coming soon" />
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
                    {todayAgenda.length === 0 ? (
                        <p className="py-8 text-center text-sm text-slate-500">No appointments scheduled for today.</p>
                    ) : (
                        <ul className="divide-y divide-slate-100 dark:divide-slate-800" role="list">
                            {todayAgenda.map((contact, i) => (
                                <li key={contact.id} className="flex items-center justify-between py-4">
                                    <div>
                                        <p className="font-medium text-slate-900 dark:text-white">
                                            {contact.firstname} {contact.lastname}
                                        </p>
                                        <p className="text-sm text-slate-500">{contact.email}</p>
                                    </div>
                                    <span className="text-sm text-slate-500">{['9:00 AM', '10:30 AM', '2:00 PM'][i % 3]}</span>
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
