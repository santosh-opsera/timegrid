import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Appointment, Business, PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

interface DashboardProps {
    businesses: Business[];
    upcomingAppointments: Appointment[];
    isOwner: boolean;
}

function statusBadgeClass(status: string): string {
    switch (status) {
        case 'reserved':
            return 'bg-yellow-100 text-yellow-800';
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'canceled':
            return 'bg-red-100 text-red-800';
        case 'served':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function formatDateTime(dateString: string): string {
    return new Date(dateString).toLocaleString(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function contactName(appointment: Appointment): string {
    if (!appointment.contact) return '';
    return [appointment.contact.firstname, appointment.contact.lastname].filter(Boolean).join(' ');
}

export default function Dashboard({ businesses, upcomingAppointments, isOwner }: DashboardProps) {
    const { auth } = usePage<PageProps>().props;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div>
                        <h3 className="text-lg font-medium text-gray-900">
                            Welcome back, {auth.user.name}!
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            {isOwner
                                ? 'Manage your businesses and upcoming appointments.'
                                : 'View your upcoming appointments.'}
                        </p>
                    </div>

                    {isOwner && (
                        <section>
                            <div className="mb-4 flex items-center justify-between">
                                <h4 className="text-base font-semibold text-gray-900">Your Businesses</h4>
                                {businesses.length > 0 && (
                                    <Link
                                        href={route('businesses.create')}
                                        className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                    >
                                        + Create Business
                                    </Link>
                                )}
                            </div>

                            {businesses.length === 0 ? (
                                <div className="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center">
                                    <p className="text-gray-500">You don&apos;t have any businesses yet.</p>
                                    <Link
                                        href={route('businesses.create')}
                                        className="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        Create Business
                                    </Link>
                                </div>
                            ) : (
                                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    {businesses.map((business) => (
                                        <div
                                            key={business.id}
                                            className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
                                        >
                                            <h5 className="font-semibold text-gray-900">{business.name}</h5>
                                            <div className="mt-2 flex gap-4 text-sm text-gray-500">
                                                <span>{business.services_count ?? 0} services</span>
                                                <span>{business.contacts_count ?? 0} contacts</span>
                                                <span>{business.appointments_count ?? 0} appts</span>
                                            </div>
                                            <div className="mt-4 flex gap-3">
                                                <Link
                                                    href={route('businesses.show', business.slug)}
                                                    className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                                >
                                                    Manage
                                                </Link>
                                                <Link
                                                    href={`/book/${business.slug}`}
                                                    className="text-sm font-medium text-gray-600 hover:text-gray-800"
                                                >
                                                    View booking page
                                                </Link>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </section>
                    )}

                    <section>
                        <h4 className="mb-4 text-base font-semibold text-gray-900">
                            {isOwner ? 'Upcoming Appointments (Your Businesses)' : 'Your Upcoming Appointments'}
                        </h4>

                        {upcomingAppointments.length === 0 ? (
                            <div className="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                                No upcoming appointments.
                            </div>
                        ) : (
                            <div className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                <ul className="divide-y divide-gray-200">
                                    {upcomingAppointments.map((appointment) => (
                                        <li key={appointment.id} className="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p className="font-medium text-gray-900">
                                                    {appointment.service?.name ?? 'Service'}
                                                    {isOwner && appointment.contact && (
                                                        <span className="ml-2 text-sm font-normal text-gray-500">
                                                            — {contactName(appointment)}
                                                        </span>
                                                    )}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    {appointment.business?.name ?? 'Business'} ·{' '}
                                                    {formatDateTime(appointment.start_at)}
                                                </p>
                                            </div>
                                            <span
                                                className={`inline-flex w-fit rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${statusBadgeClass(appointment.status)}`}
                                            >
                                                {appointment.status}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </section>

                    {!isOwner && (
                        <section className="text-center">
                            <Link
                                href={route('directory')}
                                className="inline-flex rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                            >
                                Browse businesses & book
                            </Link>
                        </section>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
