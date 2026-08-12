import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { ManagerVacanciesPageProps } from '@/types/global';
import { Head, Link, useForm } from '@inertiajs/react';
import { CalendarDaysIcon, PlusIcon } from '@heroicons/react/24/outline';
import { FormEvent } from 'react';

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

export default function ManagerVacanciesIndex({
    business,
    vacancies = [],
}: ManagerVacanciesPageProps) {
    const route = useRoute();

    const { data, setData, post, processing } = useForm({
        start_at: '09:00',
        finish_at: '17:00',
        day: 'monday',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('manager.business.vacancy.store', { business: business.slug }) as string);
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: business.name, href: route('manager.business.show', { business: business.slug }) as string },
                { label: 'Availability' },
            ]}
        >
            <Head title={`Availability — ${business.name}`} />
            <PageHeader
                title="Availability"
                description="Manage your business hours and available time slots"
                action={
                    <Link
                        href={route('manager.business.vacancy.create', { business: business.slug }) as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        <PlusIcon className="h-5 w-5" aria-hidden="true" />
                        Add availability
                    </Link>
                }
            />

            <div className="grid gap-8 lg:grid-cols-3">
                <Card className="lg:col-span-2">
                    <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Weekly schedule</h2>
                    {vacancies.length === 0 ? (
                        <EmptyState
                            title="No availability configured"
                            description="Set up your business hours to start accepting bookings."
                        />
                    ) : (
                        <div className="space-y-3">
                            {DAYS.map((day) => {
                                const dayVacancies = vacancies.filter(
                                    (v) => v.day?.toLowerCase() === day.toLowerCase(),
                                );
                                return (
                                    <div
                                        key={day}
                                        className="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-slate-700"
                                    >
                                        <div className="flex items-center gap-3">
                                            <CalendarDaysIcon className="h-5 w-5 text-brand-600" aria-hidden="true" />
                                            <span className="font-medium text-slate-900 dark:text-white">{day}</span>
                                        </div>
                                        {dayVacancies.length > 0 ? (
                                            <div className="flex flex-wrap gap-2">
                                                {dayVacancies.map((v) => (
                                                    <span
                                                        key={v.id}
                                                        className="rounded-lg bg-brand-50 px-3 py-1 text-sm font-medium text-brand-700 dark:bg-brand-950 dark:text-brand-300"
                                                    >
                                                        {v.start_at} – {v.finish_at}
                                                    </span>
                                                ))}
                                            </div>
                                        ) : (
                                            <span className="text-sm text-slate-400">Closed</span>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </Card>

                <Card>
                    <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">Quick add slot</h2>
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label htmlFor="day" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Day</label>
                            <select
                                id="day"
                                value={data.day}
                                onChange={(e) => setData('day', e.target.value)}
                                className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            >
                                {DAYS.map((d) => (
                                    <option key={d} value={d.toLowerCase()}>{d}</option>
                                ))}
                            </select>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label htmlFor="start_at" className="block text-sm font-medium text-slate-700 dark:text-slate-300">From</label>
                                <input
                                    id="start_at"
                                    type="time"
                                    value={data.start_at}
                                    onChange={(e) => setData('start_at', e.target.value)}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>
                            <div>
                                <label htmlFor="finish_at" className="block text-sm font-medium text-slate-700 dark:text-slate-300">To</label>
                                <input
                                    id="finish_at"
                                    type="time"
                                    value={data.finish_at}
                                    onChange={(e) => setData('finish_at', e.target.value)}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:opacity-50"
                        >
                            Add slot
                        </button>
                    </form>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
