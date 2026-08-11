import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useTrans } from '@/hooks/useTrans';
import { Business, Staff } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Props {
    business: Business;
    staff: Staff[];
}

export default function Index({ business, staff }: Props) {
    const { t } = useTrans();
    const [editingId, setEditingId] = useState<number | null>(null);

    const createForm = useForm({ name: '' });
    const editForm = useForm({ name: '' });

    const submitCreate: FormEventHandler = (e) => {
        e.preventDefault();
        createForm.post(route('businesses.staff.store', business.slug), {
            onSuccess: () => createForm.reset(),
        });
    };

    const startEdit = (member: Staff) => {
        setEditingId(member.id);
        editForm.setData({ name: member.name });
    };

    const submitEdit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editingId) return;

        router.put(route('businesses.staff.update', [business.slug, editingId]), editForm.data, {
            onSuccess: () => setEditingId(null),
        });
    };

    const deleteStaff = (member: Staff) => {
        if (confirm(`Remove "${member.name}" from staff?`)) {
            router.delete(route('businesses.staff.destroy', [business.slug, member.id]));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {`${t('staff.title')} — ${business.name}`}
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
            <Head title={`${t('staff.title')} — ${business.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-2xl space-y-8 sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="mb-4 text-base font-semibold text-gray-900">{t('staff.add_member')}</h3>
                        <div className="flex gap-3">
                            <div className="flex-1">
                                <InputLabel htmlFor="name" value={t('form.name')} />
                                <TextInput
                                    id="name"
                                    className="mt-1 block w-full"
                                    value={createForm.data.name}
                                    onChange={(e) => createForm.setData('name', e.target.value)}
                                    required
                                />
                                <InputError className="mt-2" message={createForm.errors.name} />
                            </div>
                            <div className="flex items-end">
                                <PrimaryButton disabled={createForm.processing}>{t('common.add')}</PrimaryButton>
                            </div>
                        </div>
                    </form>

                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        {staff.length === 0 ? (
                            <div className="p-6 text-center text-sm text-gray-500">
                                {t('staff.no_staff')}
                            </div>
                        ) : (
                            <ul className="divide-y divide-gray-200">
                                {staff.map((member) => (
                                    <li key={member.id} className="flex items-center justify-between p-4">
                                        {editingId === member.id ? (
                                            <form onSubmit={submitEdit} className="flex flex-1 gap-3">
                                                <TextInput
                                                    className="block w-full"
                                                    value={editForm.data.name}
                                                    onChange={(e) => editForm.setData('name', e.target.value)}
                                                    required
                                                />
                                                <PrimaryButton type="submit">{t('common.save')}</PrimaryButton>
                                                <SecondaryButton
                                                    type="button"
                                                    onClick={() => setEditingId(null)}
                                                >
                                                    {t('common.cancel')}
                                                </SecondaryButton>
                                            </form>
                                        ) : (
                                            <>
                                                <span className="font-medium text-gray-900">
                                                    {member.name}
                                                </span>
                                                <div className="flex gap-3">
                                                    <button
                                                        onClick={() => startEdit(member)}
                                                        className="text-sm text-indigo-600 hover:text-indigo-800"
                                                    >
                                                        {t('common.edit')}
                                                    </button>
                                                    <button
                                                        onClick={() => deleteStaff(member)}
                                                        className="text-sm text-red-600 hover:text-red-800"
                                                    >
                                                        {t('common.delete')}
                                                    </button>
                                                </div>
                                            </>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
