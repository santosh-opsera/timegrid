import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { ManagerServicesPageProps } from '@/types/global';
import { Head, Link, router } from '@inertiajs/react';
import {
    ClockIcon,
    PencilSquareIcon,
    PlusIcon,
    TrashIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';

export default function ManagerServicesIndex({ business }: ManagerServicesPageProps) {
    const route = useRoute();
    const services = business.services ?? [];

    const handleDelete = (serviceId: number) => {
        if (confirm('Are you sure you want to delete this service?')) {
            router.delete(route('manager.business.service.destroy', { business: business.slug, service: serviceId }) as string);
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: business.name, href: route('manager.business.show', { business: business.slug }) as string },
                { label: 'Services' },
            ]}
        >
            <Head title={`Services — ${business.name}`} />
            <PageHeader
                title="Services"
                description="Manage your business services and pricing"
                action={
                    <Link
                        href={route('manager.business.service.create', { business: business.slug }) as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        <PlusIcon className="h-5 w-5" aria-hidden="true" />
                        Add service
                    </Link>
                }
            />

            {services.length === 0 ? (
                <EmptyState
                    title="No services yet"
                    description="Add your first service to start accepting bookings."
                    action={
                        <Link href={route('manager.business.service.create', { business: business.slug }) as string} className="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
                            Add service
                        </Link>
                    }
                />
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {services.map((service) => (
                        <Card key={service.id}>
                            <div className="flex items-start justify-between">
                                <div className="flex items-start gap-3">
                                    <div
                                        className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white"
                                        style={{ backgroundColor: service.color ?? '#4f46e5' }}
                                    >
                                        <WrenchScrewdriverIcon className="h-5 w-5" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-slate-900 dark:text-white">{service.name}</h3>
                                        {service.description && (
                                            <p className="mt-1 line-clamp-2 text-sm text-slate-500">{service.description}</p>
                                        )}
                                        <div className="mt-2 flex items-center gap-3 text-sm text-slate-500">
                                            {service.duration && (
                                                <span className="flex items-center gap-1">
                                                    <ClockIcon className="h-4 w-4" aria-hidden="true" />
                                                    {service.duration} min
                                                </span>
                                            )}
                                            {service.price && (
                                                <span className="font-semibold text-brand-600">${service.price}</span>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="mt-4 flex gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <Link
                                    href={route('manager.business.service.edit', { business: business.slug, service: service.id }) as string}
                                    className="inline-flex flex-1 items-center justify-center gap-1 rounded-lg border border-slate-200 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                >
                                    <PencilSquareIcon className="h-4 w-4" aria-hidden="true" />
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => handleDelete(service.id)}
                                    className="inline-flex items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950"
                                    aria-label={`Delete ${service.name}`}
                                >
                                    <TrashIcon className="h-4 w-4" aria-hidden="true" />
                                </button>
                            </div>
                        </Card>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
