import StatusBadge from '@/Components/StatusBadge';
import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import GuestLayout from '@/Layouts/GuestLayout';
import useRoute from '@/Hooks/useRoute';
import { BookingShowPageProps } from '@/types/global';
import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarDaysIcon } from '@heroicons/react/24/outline';
import { format, parseISO } from 'date-fns';

import PublicBusinessShow from '@/Pages/Business/PublicShow';

export default function BookingShow({
    business,
    appointment,
    available,
}: BookingShowPageProps) {
    const route = useRoute();
    const { auth } = usePage().props;

    // Public business page (guest/user business home)
    if (business && !appointment) {
        return <PublicBusinessShow business={business} available={available} />;
    }

    const Layout = auth.user ? AuthenticatedLayout : GuestLayout;

    // Appointment confirmation
    if (appointment) {
        return (
            <Layout>
                <Head title="Booking confirmed" />
                <div className="mx-auto max-w-lg py-12 text-center">
                    <div className="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950">
                        <CheckCircleIcon className="h-10 w-10 text-emerald-600 dark:text-emerald-400" aria-hidden="true" />
                    </div>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white">Booking confirmed!</h1>
                    <p className="mt-2 text-slate-500">Your appointment has been successfully scheduled.</p>

                    <Card className="mt-8 text-left">
                        <dl className="space-y-4">
                            {appointment.code && (
                                <div className="flex justify-between">
                                    <dt className="text-sm text-slate-500">Confirmation code</dt>
                                    <dd className="font-mono text-sm font-bold text-brand-600">{appointment.code}</dd>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <dt className="text-sm text-slate-500">Service</dt>
                                <dd className="text-sm font-medium text-slate-900 dark:text-white">{appointment.service?.name}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-sm text-slate-500">Business</dt>
                                <dd className="text-sm font-medium text-slate-900 dark:text-white">{appointment.business?.name}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-sm text-slate-500">Date & time</dt>
                                <dd className="text-sm font-medium text-slate-900 dark:text-white">
                                    {format(parseISO(appointment.start_at), 'EEE, MMM d · h:mm a')}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-sm text-slate-500">Status</dt>
                                <dd><StatusBadge status={appointment.status} /></dd>
                            </div>
                        </dl>
                    </Card>

                    <Link
                        href={route('user.agenda') as string}
                        className="mt-6 inline-flex rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        View my appointments
                    </Link>
                </div>
            </Layout>
        );
    }

    return null;
}
