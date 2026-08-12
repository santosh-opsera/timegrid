import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { ManagerContactsPageProps } from '@/types/global';
import { Head, Link, router } from '@inertiajs/react';
import {
    EnvelopeIcon,
    PhoneIcon,
    PlusIcon,
    TrashIcon,
    UserCircleIcon,
} from '@heroicons/react/24/outline';

export default function ManagerContactsIndex({ business, contacts }: ManagerContactsPageProps) {
    const route = useRoute();

    const handleDelete = (contactId: number) => {
        if (confirm('Are you sure you want to remove this contact?')) {
            router.delete(route('manager.addressbook.destroy', { business: business.slug, contact: contactId }) as string);
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: business.name, href: route('manager.business.show', { business: business.slug }) as string },
                { label: 'Contacts' },
            ]}
        >
            <Head title={`Contacts — ${business.name}`} />
            <PageHeader
                title="Addressbook"
                description="Manage your customer contacts"
                action={
                    <Link
                        href={route('manager.addressbook.create', { business: business.slug }) as string}
                        className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                    >
                        <PlusIcon className="h-5 w-5" aria-hidden="true" />
                        Add contact
                    </Link>
                }
            />

            {contacts.length === 0 ? (
                <EmptyState
                    title="No contacts yet"
                    description="Add contacts to your addressbook to manage customer relationships."
                    action={
                        <Link href={route('manager.addressbook.create', { business: business.slug }) as string} className="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
                            Add contact
                        </Link>
                    }
                />
            ) : (
                <Card padding={false}>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                                <tr>
                                    <th className="px-6 py-3 font-medium text-slate-500" scope="col">Name</th>
                                    <th className="px-6 py-3 font-medium text-slate-500" scope="col">Email</th>
                                    <th className="px-6 py-3 font-medium text-slate-500" scope="col">Phone</th>
                                    <th className="px-6 py-3 font-medium text-slate-500" scope="col"><span className="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {contacts.map((contact) => (
                                    <tr key={contact.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700 dark:bg-brand-950 dark:text-brand-300">
                                                    {contact.firstname.charAt(0)}
                                                </div>
                                                <span className="font-medium text-slate-900 dark:text-white">
                                                    {contact.firstname} {contact.lastname}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-slate-500">
                                            <span className="flex items-center gap-1">
                                                <EnvelopeIcon className="h-4 w-4" aria-hidden="true" />
                                                {contact.email}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-slate-500">
                                            {contact.phone || contact.mobile ? (
                                                <span className="flex items-center gap-1">
                                                    <PhoneIcon className="h-4 w-4" aria-hidden="true" />
                                                    {contact.phone || contact.mobile}
                                                </span>
                                            ) : '—'}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex justify-end gap-2">
                                                <Link
                                                    href={route('manager.addressbook.show', { business: business.slug, contact: contact.id }) as string}
                                                    className="rounded-lg px-3 py-1.5 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-950"
                                                >
                                                    View
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(contact.id)}
                                                    className="rounded-lg p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-950"
                                                    aria-label={`Delete ${contact.firstname}`}
                                                >
                                                    <TrashIcon className="h-4 w-4" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>
            )}
        </AuthenticatedLayout>
    );
}
