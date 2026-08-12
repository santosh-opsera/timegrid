import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { ManagerStaffPageProps } from '@/types/global';
import { Head, Link, router } from '@inertiajs/react';
import {
    EnvelopeIcon,
    PlusIcon,
    TrashIcon,
    UserGroupIcon,
} from '@heroicons/react/24/outline';

export default function ManagerStaffIndex({
    business,
    humanresources = [],
}: ManagerStaffPageProps) {
    const route = useRoute();

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to remove this staff member?')) {
            router.delete(route('manager.business.humanresource.destroy', { business: business.slug, humanresource: id }) as string);
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: business.name, href: route('manager.business.show', { business: business.slug }) as string },
                { label: 'Staff' },
            ]}
        >
            <Head title={`Staff — ${business.name}`} />
            <PageHeader
                title="Staff"
                description="Manage your team members and their schedules"
                action={
                    <Link
                        href={route('manager.business.humanresource.create', { business: business.slug }) as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        <PlusIcon className="h-5 w-5" aria-hidden="true" />
                        Add staff
                    </Link>
                }
            />

            {humanresources.length === 0 ? (
                <EmptyState
                    title="No staff members"
                    description="Add team members to assign appointments and manage schedules."
                    action={
                        <Link href={route('manager.business.humanresource.create', { business: business.slug }) as string} className="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
                            Add staff member
                        </Link>
                    }
                />
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {humanresources.map((staff) => (
                        <Card key={staff.id}>
                            <div className="flex items-start gap-4">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-950">
                                    <UserGroupIcon className="h-6 w-6 text-brand-600 dark:text-brand-400" aria-hidden="true" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <h3 className="font-semibold text-slate-900 dark:text-white">{staff.name}</h3>
                                    {staff.email && (
                                        <p className="mt-1 flex items-center gap-1 truncate text-sm text-slate-500">
                                            <EnvelopeIcon className="h-4 w-4 shrink-0" aria-hidden="true" />
                                            {staff.email}
                                        </p>
                                    )}
                                </div>
                            </div>
                            <div className="mt-4 flex gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <Link
                                    href={route('manager.business.humanresource.edit', { business: business.slug, humanresource: staff.id }) as string}
                                    className="flex-1 rounded-lg border border-slate-200 py-2 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => handleDelete(staff.id)}
                                    className="rounded-lg border border-red-200 px-3 py-2 text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950"
                                    aria-label={`Remove ${staff.name}`}
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
