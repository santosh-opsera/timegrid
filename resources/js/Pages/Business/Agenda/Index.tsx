import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useTrans } from '@/hooks/useTrans';
import { Appointment, Business } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

interface Props {
    business: Business;
    appointments: Appointment[];
    date: string;
}

function statusBadgeClass(status: string): string {
    switch (status) {
        case 'reserved':
        case 'R':
            return 'bg-amber-100 text-amber-800';
        case 'confirmed':
        case 'C':
            return 'bg-green-100 text-green-800';
        case 'canceled':
        case 'A':
            return 'bg-red-100 text-red-800';
        case 'served':
        case 'S':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function statusLabel(status: string): string {
    switch (status) {
        case 'R':
            return 'reserved';
        case 'C':
            return 'confirmed';
        case 'A':
            return 'canceled';
        case 'S':
            return 'served';
        default:
            return status;
    }
}

function formatTime(dateString: string): string {
    return new Date(dateString).toLocaleTimeString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
    });
}

function contactName(appointment: Appointment): string {
    if (!appointment.contact) return 'Guest';
    return [appointment.contact.firstname, appointment.contact.lastname].filter(Boolean).join(' ');
}

export default function Index({ business, appointments, date }: Props) {
    const { t } = useTrans();

    const changeDate = (newDate: string) => {
        router.get(route('businesses.agenda.index', business.slug), { date: newDate });
    };

    const appointmentAction = (appointment: Appointment, action: string) => {
        router.post(route('businesses.appointments.action', [business.slug, appointment.id]), {
            action,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {`${t('agenda.title')} — ${business.name}`}
                    </h2>
                    <div className="flex items-center gap-4">
                        <Link
                            href={route('businesses.agenda.calendar', business.slug)}
                            className="text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            {t('agenda.calendar_view')}
                        </Link>
                        <Link
                            href={route('businesses.show', business.slug)}
                            className="text-sm text-gray-600 hover:text-gray-800"
                        >
                            {`← ${t('common.back_dashboard')}`}
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title={`${t('agenda.title')} — ${business.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <label htmlFor="date" className="block text-sm font-medium text-gray-700">
                            {t('form.date')}
                        </label>
                        <input
                            id="date"
                            type="date"
                            value={date}
                            onChange={(e) => changeDate(e.target.value)}
                            className="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    {appointments.length === 0 ? (
                        <div className="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                            {t('agenda.no_appointments')}
                        </div>
                    ) : (
                        <div className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            {t('common.time')}
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            {t('common.customer')}
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            {t('common.service')}
                                        </th>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                            {t('common.status')}
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">
                                            {t('common.actions')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {appointments.map((appointment) => (
                                        <tr key={appointment.id}>
                                            <td className="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                                {formatTime(appointment.start_at)}
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
                                                    {statusLabel(appointment.status)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right text-sm">
                                                {(appointment.status === 'reserved' ||
                                                    appointment.status === 'R') && (
                                                    <button
                                                        onClick={() =>
                                                            appointmentAction(appointment, 'confirm')
                                                        }
                                                        className="mr-2 rounded bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700"
                                                    >
                                                        {t('common.confirm')}
                                                    </button>
                                                )}
                                                {(appointment.status === 'reserved' ||
                                                    appointment.status === 'R' ||
                                                    appointment.status === 'confirmed' ||
                                                    appointment.status === 'C') && (
                                                    <button
                                                        onClick={() =>
                                                            appointmentAction(appointment, 'cancel')
                                                        }
                                                        className="mr-2 rounded bg-red-600 px-2 py-1 text-xs text-white hover:bg-red-700"
                                                    >
                                                        {t('common.cancel')}
                                                    </button>
                                                )}
                                                {(appointment.status === 'confirmed' ||
                                                    appointment.status === 'C') && (
                                                    <button
                                                        onClick={() =>
                                                            appointmentAction(appointment, 'serve')
                                                        }
                                                        className="rounded bg-blue-600 px-2 py-1 text-xs text-white hover:bg-blue-700"
                                                    >
                                                        {t('common.serve')}
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
