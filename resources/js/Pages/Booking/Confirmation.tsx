import { useTrans } from '@/hooks/useTrans';
import { Appointment, Business } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface ConfirmationProps {
    business: Business;
    appointment: Appointment;
}

function formatDateTime(isoString: string): string {
    const date = new Date(isoString);
    return date.toLocaleString(undefined, {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function formatStatus(status: string, statusLabel?: string): string {
    const raw = statusLabel || status;
    const normalized =
        raw === 'R'
            ? 'reserved'
            : raw === 'C'
              ? 'confirmed'
              : raw === 'A'
                ? 'canceled'
                : raw === 'S'
                  ? 'served'
                  : raw;

    return normalized.charAt(0).toUpperCase() + normalized.slice(1).replace(/_/g, ' ');
}

export default function Confirmation({ business, appointment }: ConfirmationProps) {
    const { t } = useTrans();
    const contact = appointment.contact;
    const service = appointment.service;

    return (
        <>
            <Head title={`Booking Confirmed — ${business.name}`} />

            <div className="min-h-screen bg-gradient-to-b from-indigo-50 to-white">
                <header className="border-b border-indigo-100 bg-white/80 backdrop-blur">
                    <div className="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6">
                        <Link href="/" className="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700">
                            <span>←</span>
                            <span>TimeGrid</span>
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6 sm:py-12">
                    <div className="text-center">
                        <h1 className="text-2xl font-bold text-gray-900 sm:text-3xl">{business.name}</h1>
                    </div>

                    <div className="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <div className="text-center">
                            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <svg
                                    className="h-8 w-8 text-green-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth={2.5}
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 className="mt-4 text-2xl font-bold text-gray-900">{t('confirmation.title')}</h2>
                            <p className="mt-2 text-sm text-gray-500">
                                {t('confirmation.subtitle')}
                            </p>
                        </div>

                        <div className="mt-8 space-y-6">
                            <section>
                                <h3 className="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    {t('confirmation.appointment')}
                                </h3>
                                <dl className="mt-3 divide-y divide-gray-100 rounded-xl border border-gray-200">
                                    <div className="flex justify-between px-4 py-3">
                                        <dt className="text-sm text-gray-500">{t('confirmation.business')}</dt>
                                        <dd className="text-sm font-medium text-gray-900">{business.name}</dd>
                                    </div>
                                    {service && (
                                        <div className="flex justify-between px-4 py-3">
                                            <dt className="text-sm text-gray-500">{t('confirmation.service')}</dt>
                                            <dd className="text-sm font-medium text-gray-900">{service.name}</dd>
                                        </div>
                                    )}
                                    <div className="flex justify-between px-4 py-3">
                                        <dt className="text-sm text-gray-500">{t('confirmation.date_time')}</dt>
                                        <dd className="text-right text-sm font-medium text-gray-900">
                                            {formatDateTime(appointment.start_at)}
                                        </dd>
                                    </div>
                                    <div className="flex justify-between px-4 py-3">
                                        <dt className="text-sm text-gray-500">{t('confirmation.duration')}</dt>
                                        <dd className="text-sm font-medium text-gray-900">
                                            {appointment.duration} {t('booking.min')}
                                        </dd>
                                    </div>
                                    <div className="flex justify-between px-4 py-3">
                                        <dt className="text-sm text-gray-500">{t('confirmation.status')}</dt>
                                        <dd>
                                            <span className="inline-flex rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                {formatStatus(appointment.status, appointment.status_label)}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </section>

                            {contact && (
                                <section>
                                    <h3 className="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        {t('confirmation.contact_details')}
                                    </h3>
                                    <dl className="mt-3 divide-y divide-gray-100 rounded-xl border border-gray-200">
                                        <div className="flex justify-between px-4 py-3">
                                            <dt className="text-sm text-gray-500">{t('confirmation.name')}</dt>
                                            <dd className="text-sm font-medium text-gray-900">
                                                {[contact.firstname, contact.lastname].filter(Boolean).join(' ')}
                                            </dd>
                                        </div>
                                        {contact.email && (
                                            <div className="flex justify-between px-4 py-3">
                                                <dt className="text-sm text-gray-500">{t('confirmation.email')}</dt>
                                                <dd className="text-sm font-medium text-gray-900">{contact.email}</dd>
                                            </div>
                                        )}
                                        {contact.phone && (
                                            <div className="flex justify-between px-4 py-3">
                                                <dt className="text-sm text-gray-500">{t('confirmation.phone')}</dt>
                                                <dd className="text-sm font-medium text-gray-900">{contact.phone}</dd>
                                            </div>
                                        )}
                                    </dl>
                                </section>
                            )}
                        </div>

                        <div className="mt-8 text-center">
                            <Link
                                href="/"
                                className="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                            >
                                {t('confirmation.back_home')}
                            </Link>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
