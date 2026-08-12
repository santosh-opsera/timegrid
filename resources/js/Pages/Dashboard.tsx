import { useTrans } from '@/hooks/useTrans';
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
        case 'R':
            return 'bg-yellow-100 text-yellow-800';
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
    const { t } = useTrans();

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {t('dashboard.title')}
                </h2>
            }
        >
            <Head title={t('dashboard.title')} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div>
                        <h3 className="text-lg font-medium text-gray-900">
                            {t('dashboard.welcome', { name: auth.user.name })}
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            {isOwner ? t('dashboard.manage_desc') : t('dashboard.view_desc')}
                        </p>
                    </div>

                    {isOwner && (
                        <section>
                            <div className="mb-4 flex items-center justify-between">
                                <h4 className="text-base font-semibold text-gray-900">{t('dashboard.your_businesses')}</h4>
                                {businesses.length > 0 && (
                                    <Link
                                        href={route('businesses.create')}
                                        className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                    >
                                        {t('dashboard.create_business')}
                                    </Link>
                                )}
                            </div>

                            {businesses.length === 0 ? (
                                <div className="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center">
                                    <p className="text-gray-500">{t('dashboard.no_businesses')}</p>
                                    <Link
                                        href={route('businesses.create')}
                                        className="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    >
                                        {t('dashboard.create_business_btn')}
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
                                                <span>{business.services_count ?? 0} {t('dashboard.services')}</span>
                                                <span>{business.contacts_count ?? 0} {t('dashboard.contacts')}</span>
                                                <span>{business.appointments_count ?? 0} {t('dashboard.appts')}</span>
                                            </div>
                                            <div className="mt-4 flex gap-3">
                                                <Link
                                                    href={route('businesses.show', business.slug)}
                                                    className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                                >
                                                    {t('dashboard.manage')}
                                                </Link>
                                                <Link
                                                    href={`/book/${business.slug}`}
                                                    className="text-sm font-medium text-gray-600 hover:text-gray-800"
                                                >
                                                    {t('dashboard.view_booking')}
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
                            {isOwner ? t('dashboard.upcoming_owner') : t('dashboard.upcoming_customer')}
                        </h4>

                        {upcomingAppointments.length === 0 ? (
                            <div className="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                                {t('dashboard.no_appointments')}
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
                                                {statusLabel(appointment.status)}
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
                                {t('dashboard.browse_book')}
                            </Link>
                        </section>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
