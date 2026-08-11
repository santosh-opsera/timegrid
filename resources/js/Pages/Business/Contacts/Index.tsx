import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useTrans } from '@/hooks/useTrans';
import { Business, Contact } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface PaginatedContacts {
    data: Contact[];
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    business: Business;
    contacts: PaginatedContacts;
}

export default function Index({ business, contacts }: Props) {
    const { t } = useTrans();
    const [editingId, setEditingId] = useState<number | null>(null);

    const createForm = useForm({
        firstname: '',
        lastname: '',
        email: '',
        phone: '',
        notes: '',
    });

    const editForm = useForm({
        firstname: '',
        lastname: '',
        email: '',
        phone: '',
        notes: '',
    });

    const submitCreate: FormEventHandler = (e) => {
        e.preventDefault();
        createForm.post(route('businesses.contacts.store', business.slug), {
            onSuccess: () => createForm.reset(),
        });
    };

    const startEdit = (contact: Contact) => {
        setEditingId(contact.id);
        editForm.setData({
            firstname: contact.firstname,
            lastname: contact.lastname ?? '',
            email: contact.email ?? '',
            phone: contact.phone ?? '',
            notes: contact.notes ?? '',
        });
    };

    const submitEdit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editingId) return;

        router.put(route('businesses.contacts.update', [business.slug, editingId]), editForm.data, {
            onSuccess: () => setEditingId(null),
        });
    };

    const deleteContact = (contact: Contact) => {
        const name = [contact.firstname, contact.lastname].filter(Boolean).join(' ');
        if (confirm(`Delete contact "${name}"?`)) {
            router.delete(route('businesses.contacts.destroy', [business.slug, contact.id]));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {`${t('contacts.title')} — ${business.name}`}
                    </h2>
                    <Link
                        href={route('businesses.show', business.slug)}
                        className="text-sm text-gray-600 hover:text-gray-800"
                    >
                        {`← ${t('common.back_dashboard')}`}
                    </Link>
                </div>
            }
        >
            <Head title={`${t('contacts.title')} — ${business.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-5xl space-y-8 sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="mb-4 text-base font-semibold text-gray-900">{t('contacts.add_contact')}</h3>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel htmlFor="firstname" value={t('form.first_name')} />
                                <TextInput
                                    id="firstname"
                                    className="mt-1 block w-full"
                                    value={createForm.data.firstname}
                                    onChange={(e) => createForm.setData('firstname', e.target.value)}
                                    required
                                />
                                <InputError className="mt-2" message={createForm.errors.firstname} />
                            </div>
                            <div>
                                <InputLabel htmlFor="lastname" value={t('form.last_name')} />
                                <TextInput
                                    id="lastname"
                                    className="mt-1 block w-full"
                                    value={createForm.data.lastname}
                                    onChange={(e) => createForm.setData('lastname', e.target.value)}
                                />
                                <InputError className="mt-2" message={createForm.errors.lastname} />
                            </div>
                            <div>
                                <InputLabel htmlFor="email" value={t('form.email')} />
                                <TextInput
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    value={createForm.data.email}
                                    onChange={(e) => createForm.setData('email', e.target.value)}
                                />
                                <InputError className="mt-2" message={createForm.errors.email} />
                            </div>
                            <div>
                                <InputLabel htmlFor="phone" value={t('form.phone')} />
                                <TextInput
                                    id="phone"
                                    className="mt-1 block w-full"
                                    value={createForm.data.phone}
                                    onChange={(e) => createForm.setData('phone', e.target.value)}
                                />
                                <InputError className="mt-2" message={createForm.errors.phone} />
                            </div>
                            <div className="sm:col-span-2">
                                <InputLabel htmlFor="notes" value={t('form.notes')} />
                                <textarea
                                    id="notes"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows={2}
                                    value={createForm.data.notes}
                                    onChange={(e) => createForm.setData('notes', e.target.value)}
                                />
                                <InputError className="mt-2" message={createForm.errors.notes} />
                            </div>
                        </div>
                        <div className="mt-4">
                            <PrimaryButton disabled={createForm.processing}>{t('contacts.add_contact')}</PrimaryButton>
                        </div>
                    </form>

                    <div className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        {t('form.first_name')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        {t('form.last_name')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        {t('form.email')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        {t('form.phone')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                                        {t('form.notes')}
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">
                                        {t('common.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {contacts.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={6}
                                            className="px-4 py-6 text-center text-sm text-gray-500"
                                        >
                                            {t('contacts.no_contacts')}
                                        </td>
                                    </tr>
                                ) : (
                                    contacts.data.map((contact) => (
                                        <tr key={contact.id}>
                                            {editingId === contact.id ? (
                                                <>
                                                    <td className="px-4 py-3">
                                                        <TextInput
                                                            className="block w-full"
                                                            value={editForm.data.firstname}
                                                            onChange={(e) =>
                                                                editForm.setData('firstname', e.target.value)
                                                            }
                                                            required
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <TextInput
                                                            className="block w-full"
                                                            value={editForm.data.lastname}
                                                            onChange={(e) =>
                                                                editForm.setData('lastname', e.target.value)
                                                            }
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <TextInput
                                                            type="email"
                                                            className="block w-full"
                                                            value={editForm.data.email}
                                                            onChange={(e) =>
                                                                editForm.setData('email', e.target.value)
                                                            }
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <TextInput
                                                            className="block w-full"
                                                            value={editForm.data.phone}
                                                            onChange={(e) =>
                                                                editForm.setData('phone', e.target.value)
                                                            }
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <TextInput
                                                            className="block w-full"
                                                            value={editForm.data.notes}
                                                            onChange={(e) =>
                                                                editForm.setData('notes', e.target.value)
                                                            }
                                                        />
                                                    </td>
                                                    <td className="px-4 py-3 text-right">
                                                        <form onSubmit={submitEdit} className="inline-flex gap-2">
                                                            <PrimaryButton type="submit">{t('common.save')}</PrimaryButton>
                                                            <SecondaryButton
                                                                type="button"
                                                                onClick={() => setEditingId(null)}
                                                            >
                                                                {t('common.cancel')}
                                                            </SecondaryButton>
                                                        </form>
                                                    </td>
                                                </>
                                            ) : (
                                                <>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {contact.firstname}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {contact.lastname ?? '—'}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-500">
                                                        {contact.email ?? '—'}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-500">
                                                        {contact.phone ?? '—'}
                                                    </td>
                                                    <td className="max-w-xs truncate px-4 py-3 text-sm text-gray-500">
                                                        {contact.notes ?? '—'}
                                                    </td>
                                                    <td className="px-4 py-3 text-right text-sm">
                                                        <button
                                                            onClick={() => startEdit(contact)}
                                                            className="mr-2 text-indigo-600 hover:text-indigo-800"
                                                        >
                                                            {t('common.edit')}
                                                        </button>
                                                        <button
                                                            onClick={() => deleteContact(contact)}
                                                            className="text-red-600 hover:text-red-800"
                                                        >
                                                            {t('common.delete')}
                                                        </button>
                                                    </td>
                                                </>
                                            )}
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {contacts.links.length > 3 && (
                        <div className="flex justify-center gap-1">
                            {contacts.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    className={`rounded px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : link.url
                                              ? 'bg-white text-gray-700 hover:bg-gray-50'
                                              : 'cursor-not-allowed text-gray-400'
                                    }`}
                                    preserveScroll
                                >
                                    <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
