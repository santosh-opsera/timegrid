import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useTrans } from '@/hooks/useTrans';
import { Business, Service } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Props {
    business: Business;
    services: Service[];
}

export default function Index({ business, services }: Props) {
    const { t } = useTrans();
    const [editingId, setEditingId] = useState<number | null>(null);

    const createForm = useForm({
        name: '',
        description: '',
        duration: 30,
        color: '#3B82F6',
    });

    const editForm = useForm({
        name: '',
        description: '',
        duration: 30,
        color: '#3B82F6',
        is_active: true,
    });

    const submitCreate: FormEventHandler = (e) => {
        e.preventDefault();
        createForm.post(route('businesses.services.store', business.slug), {
            onSuccess: () => {
                createForm.reset();
            },
        });
    };

    const startEdit = (service: Service) => {
        setEditingId(service.id);
        editForm.setData({
            name: service.name,
            description: service.description ?? '',
            duration: service.duration,
            color: service.color,
            is_active: service.is_active,
        });
    };

    const submitEdit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editingId) return;

        router.put(route('businesses.services.update', [business.slug, editingId]), editForm.data, {
            onSuccess: () => setEditingId(null),
        });
    };

    const toggleActive = (service: Service) => {
        router.put(route('businesses.services.update', [business.slug, service.id]), {
            name: service.name,
            description: service.description,
            duration: service.duration,
            color: service.color,
            is_active: !service.is_active,
        });
    };

    const deleteService = (service: Service) => {
        if (confirm(`Delete service "${service.name}"?`)) {
            router.delete(route('businesses.services.destroy', [business.slug, service.id]));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {`${t('services.title')} — ${business.name}`}
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
            <Head title={`${t('services.title')} — ${business.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl space-y-8 sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="mb-4 text-base font-semibold text-gray-900">{t('services.add_service')}</h3>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
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
                            <div>
                                <InputLabel htmlFor="duration" value={t('form.duration_minutes')} />
                                <TextInput
                                    id="duration"
                                    type="number"
                                    min={5}
                                    max={480}
                                    className="mt-1 block w-full"
                                    value={createForm.data.duration}
                                    onChange={(e) =>
                                        createForm.setData('duration', parseInt(e.target.value) || 0)
                                    }
                                    required
                                />
                                <InputError className="mt-2" message={createForm.errors.duration} />
                            </div>
                            <div>
                                <InputLabel htmlFor="color" value={t('form.color')} />
                                <input
                                    id="color"
                                    type="color"
                                    className="mt-1 h-10 w-full cursor-pointer rounded-md border border-gray-300"
                                    value={createForm.data.color}
                                    onChange={(e) => createForm.setData('color', e.target.value)}
                                />
                                <InputError className="mt-2" message={createForm.errors.color} />
                            </div>
                            <div>
                                <InputLabel htmlFor="description" value={t('form.description')} />
                                <TextInput
                                    id="description"
                                    className="mt-1 block w-full"
                                    value={createForm.data.description}
                                    onChange={(e) => createForm.setData('description', e.target.value)}
                                />
                                <InputError className="mt-2" message={createForm.errors.description} />
                            </div>
                        </div>
                        <div className="mt-4">
                            <PrimaryButton disabled={createForm.processing}>{t('services.add_service')}</PrimaryButton>
                        </div>
                    </form>

                    <div className="space-y-4">
                        {services.length === 0 ? (
                            <div className="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                                {t('services.no_services')}
                            </div>
                        ) : (
                            services.map((service) => (
                                <div
                                    key={service.id}
                                    className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm"
                                >
                                    {editingId === service.id ? (
                                        <form onSubmit={submitEdit} className="space-y-4">
                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <div>
                                                    <InputLabel value={t('form.name')} />
                                                    <TextInput
                                                        className="mt-1 block w-full"
                                                        value={editForm.data.name}
                                                        onChange={(e) =>
                                                            editForm.setData('name', e.target.value)
                                                        }
                                                        required
                                                    />
                                                </div>
                                                <div>
                                                    <InputLabel value={t('form.duration_minutes')} />
                                                    <TextInput
                                                        type="number"
                                                        min={5}
                                                        max={480}
                                                        className="mt-1 block w-full"
                                                        value={editForm.data.duration}
                                                        onChange={(e) =>
                                                            editForm.setData(
                                                                'duration',
                                                                parseInt(e.target.value) || 0,
                                                            )
                                                        }
                                                        required
                                                    />
                                                </div>
                                                <div>
                                                    <InputLabel value={t('form.color')} />
                                                    <input
                                                        type="color"
                                                        className="mt-1 h-10 w-full cursor-pointer rounded-md border border-gray-300"
                                                        value={editForm.data.color}
                                                        onChange={(e) =>
                                                            editForm.setData('color', e.target.value)
                                                        }
                                                    />
                                                </div>
                                                <div>
                                                    <InputLabel value={t('form.description')} />
                                                    <TextInput
                                                        className="mt-1 block w-full"
                                                        value={editForm.data.description}
                                                        onChange={(e) =>
                                                            editForm.setData('description', e.target.value)
                                                        }
                                                    />
                                                </div>
                                            </div>
                                            <div className="flex gap-2">
                                                <PrimaryButton type="submit">{t('common.save')}</PrimaryButton>
                                                <SecondaryButton
                                                    type="button"
                                                    onClick={() => setEditingId(null)}
                                                >
                                                    {t('common.cancel')}
                                                </SecondaryButton>
                                            </div>
                                        </form>
                                    ) : (
                                        <div className="flex items-start justify-between gap-4">
                                            <div className="flex items-start gap-3">
                                                <div
                                                    className="mt-1 h-8 w-8 shrink-0 rounded-md"
                                                    style={{ backgroundColor: service.color }}
                                                />
                                                <div>
                                                    <h4 className="font-semibold text-gray-900">
                                                        {service.name}
                                                    </h4>
                                                    <p className="text-sm text-gray-500">
                                                        {service.duration} {t('common.minutes')}
                                                    </p>
                                                    {service.description && (
                                                        <p className="mt-1 text-sm text-gray-600">
                                                            {service.description}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <label className="flex items-center gap-2 text-sm text-gray-600">
                                                    <input
                                                        type="checkbox"
                                                        checked={service.is_active}
                                                        onChange={() => toggleActive(service)}
                                                        className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    />
                                                    {t('common.active')}
                                                </label>
                                                <button
                                                    onClick={() => startEdit(service)}
                                                    className="text-sm text-indigo-600 hover:text-indigo-800"
                                                >
                                                    {t('common.edit')}
                                                </button>
                                                <button
                                                    onClick={() => deleteService(service)}
                                                    className="text-sm text-red-600 hover:text-red-800"
                                                >
                                                    {t('common.delete')}
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
