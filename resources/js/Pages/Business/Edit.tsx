import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useTrans } from '@/hooks/useTrans';
import { Business } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const TIMEZONES = [
    'UTC',
    'America/New_York',
    'America/Chicago',
    'America/Denver',
    'America/Los_Angeles',
    'Europe/London',
    'Europe/Paris',
    'Europe/Berlin',
    'Asia/Tokyo',
    'Asia/Kolkata',
    'Australia/Sydney',
];

interface Props {
    business: Business;
}

export default function Edit({ business }: Props) {
    const { t } = useTrans();
    const { data, setData, put, errors, processing } = useForm({
        name: business.name,
        description: business.description ?? '',
        category: business.category ?? '',
        timezone: business.timezone,
        strategy: business.strategy,
        phone: business.phone ?? '',
        postal_address: business.postal_address ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('businesses.update', business.slug));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    {`${t('common.edit')} ${business.name}`}
                </h2>
            }
        >
            <Head title={`${t('common.edit')} ${business.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                        <div>
                            <InputLabel htmlFor="name" value={t('form.name')} />
                            <TextInput
                                id="name"
                                className="mt-1 block w-full"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                required
                            />
                            <InputError className="mt-2" message={errors.name} />
                        </div>

                        <div>
                            <InputLabel htmlFor="description" value={t('form.description')} />
                            <textarea
                                id="description"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows={3}
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                            />
                            <InputError className="mt-2" message={errors.description} />
                        </div>

                        <div>
                            <InputLabel htmlFor="category" value={t('form.category')} />
                            <TextInput
                                id="category"
                                className="mt-1 block w-full"
                                value={data.category}
                                onChange={(e) => setData('category', e.target.value)}
                            />
                            <InputError className="mt-2" message={errors.category} />
                        </div>

                        <div>
                            <InputLabel htmlFor="timezone" value={t('form.timezone')} />
                            <select
                                id="timezone"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value={data.timezone}
                                onChange={(e) => setData('timezone', e.target.value)}
                            >
                                {TIMEZONES.map((tz) => (
                                    <option key={tz} value={tz}>
                                        {tz}
                                    </option>
                                ))}
                            </select>
                            <InputError className="mt-2" message={errors.timezone} />
                        </div>

                        <div>
                            <InputLabel value={t('form.booking_strategy')} />
                            <div className="mt-2 flex gap-6">
                                <label className="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="strategy"
                                        value="timeslot"
                                        checked={data.strategy === 'timeslot'}
                                        onChange={(e) => setData('strategy', e.target.value)}
                                        className="text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span className="text-sm text-gray-700">{t('form.timeslot')}</span>
                                </label>
                                <label className="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="strategy"
                                        value="dateslot"
                                        checked={data.strategy === 'dateslot'}
                                        onChange={(e) => setData('strategy', e.target.value)}
                                        className="text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span className="text-sm text-gray-700">{t('form.dateslot')}</span>
                                </label>
                            </div>
                            <InputError className="mt-2" message={errors.strategy} />
                        </div>

                        <div>
                            <InputLabel htmlFor="phone" value={t('form.phone')} />
                            <TextInput
                                id="phone"
                                className="mt-1 block w-full"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                            />
                            <InputError className="mt-2" message={errors.phone} />
                        </div>

                        <div>
                            <InputLabel htmlFor="postal_address" value={t('form.postal_address')} />
                            <textarea
                                id="postal_address"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows={2}
                                value={data.postal_address}
                                onChange={(e) => setData('postal_address', e.target.value)}
                            />
                            <InputError className="mt-2" message={errors.postal_address} />
                        </div>

                        <PrimaryButton disabled={processing}>{t('business.save_changes')}</PrimaryButton>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
