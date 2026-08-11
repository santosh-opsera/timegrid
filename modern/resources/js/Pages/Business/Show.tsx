import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Appointment, Business } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

interface Props {
    business: Business;
    stats: {
        total_appointments: number;
        upcoming: number;
        contacts: number;
        services: number;
    };
    recentAppointments: Appointment[];
}

function statusBadgeClass(status: string): string {
    switch (status) {
        case 'reserved':
            return 'bg-amber-100 text-amber-800';
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
    if (!appointment.contact) return 'Guest';
    return [appointment.contact.firstname, appointment.contact.lastname].filter(Boolean).join(' ');
}

const quickActions = [
    { label: 'Services', route: 'businesses.services.index', icon: '🛎️' },
    { label: 'Staff', route: 'businesses.staff.index', icon: '👥' },
    { label: 'Contacts', route: 'businesses.contacts.index', icon: '📇' },
    { label: 'Vacancies', route: 'businesses.vacancies.index', icon: '📅' },
    { label: 'Agenda', route: 'businesses.agenda.index', icon: '📋' },
    { label: 'Calendar', route: 'businesses.agenda.calendar', icon: '🗓️' },
];

export default function Show({ business, stats, recentAppointments }: Props) {
    const appointmentAction = (appointment: Appointment, action: string) => {
        router.post(route('businesses.appointments.action', [business.slug, appointment.id]), { action });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            {business.name}
                        </h2>
                        {business.description && (
                            <p className="mt-1 text-sm text-gray-500">{business.description}</p>
                        )}
                    </div>
                    <Link
                        href={route('businesses.edit', business.slug)}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                    >
                        Edit Business
                    </Link>
                </div>
            }
        >
            <Head title={business.name} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p className="text-sm text-gray-500">Total Appointments</p>
                            <p className="mt-1 text-2xl font-semibold text-gray-900">
                                {stats.total_appointments}
                            </p>
                        </div>
                        <div className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p className="text-sm text-gray-500">Upcoming</p>
                            <p className="mt-1 text-2xl font-semibold text-gray-900">{stats.upcoming}</p>
                        </div>
                        <div className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p className="text-sm text-gray-500">Contacts</p>
                            <p className="mt-1 text-2xl font-semibold text-gray-900">{stats.contacts}</p>
                        </div>
                        <div className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <p className="text-sm text-gray-500">Services</p>
                            <p className="mt-1 text-2xl font-semibold text-gray-900">{stats.services}</p>
                        </div>
                    </div>

                    <section>
                        <h3 className="mb-4 text-base font-semibold text-gray-900">Quick Actions</h3>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {quickActions.map((action) => (
                                <Link
                                    key={action.label}
                                    href={route(action.route, business.slug)}
                                    className="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                                >
                                    <span className="text-2xl">{action.icon}</span>
                                    <span className="font-medium text-gray-900">{action.label}</span>
                                </Link>
                            ))}
                        </div>
                    </section>

                    <section>
                        <h3 className="mb-4 text-base font-semibold text-gray-900">
                            Recent Appointments
                        </h3>

                        {recentAppointments.length === 0 ? (
                            <div className="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                                No appointments yet.
                            </div>
                        ) : (
                            <div className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Date / Time
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Customer
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Service
                                            </th>
                                            <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {recentAppointments.map((appointment) => (
                                            <tr key={appointment.id}>
                                                <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                                    {formatDateTime(appointment.start_at)}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-gray-900">
                                                    {contactName(appointment)}
                                                </td>
                                                <td className="px-4 py-3 text-sm text-gray-500">
                                                    {appointment.service?.name ?? '—'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span
                                                        className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${statusBadgeClass(appointment.status)}`}
                                                    >
                                                        {appointment.status}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-3 text-right text-sm">
                                                    {appointment.status === 'reserved' && (
                                                        <button
                                                            onClick={() =>
                                                                appointmentAction(appointment, 'confirm')
                                                            }
                                                            className="mr-2 text-green-600 hover:text-green-800"
                                                        >
                                                            Confirm
                                                        </button>
                                                    )}
                                                    {(appointment.status === 'reserved' ||
                                                        appointment.status === 'confirmed') && (
                                                        <button
                                                            onClick={() =>
                                                                appointmentAction(appointment, 'cancel')
                                                            }
                                                            className="mr-2 text-red-600 hover:text-red-800"
                                                        >
                                                            Cancel
                                                        </button>
                                                    )}
                                                    {appointment.status === 'confirmed' && (
                                                        <button
                                                            onClick={() =>
                                                                appointmentAction(appointment, 'serve')
                                                            }
                                                            className="text-blue-600 hover:text-blue-800"
                                                        >
                                                            Serve
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
