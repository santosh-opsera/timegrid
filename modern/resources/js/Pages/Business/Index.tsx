import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Business } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Props {
    businesses: Business[];
}

export default function Index({ businesses }: Props) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        My Businesses
                    </h2>
                    <Link
                        href={route('businesses.create')}
                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Create Business
                    </Link>
                </div>
            }
        >
            <Head title="My Businesses" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {businesses.length === 0 ? (
                        <div className="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center">
                            <p className="text-gray-500">You don&apos;t have any businesses yet.</p>
                            <Link
                                href={route('businesses.create')}
                                className="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Create Business
                            </Link>
                        </div>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {businesses.map((business) => (
                                <Link
                                    key={business.id}
                                    href={route('businesses.show', business.slug)}
                                    className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                                >
                                    <h3 className="font-semibold text-gray-900">{business.name}</h3>
                                    {business.description && (
                                        <p className="mt-1 line-clamp-2 text-sm text-gray-500">
                                            {business.description}
                                        </p>
                                    )}
                                    <div className="mt-4 flex gap-4 text-sm text-gray-600">
                                        <span>{business.services_count ?? 0} services</span>
                                        <span>{business.contacts_count ?? 0} contacts</span>
                                        <span>{business.appointments_count ?? 0} appointments</span>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
