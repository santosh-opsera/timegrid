import LanguageSwitcher from '@/Components/LanguageSwitcher';
import { useTrans } from '@/hooks/useTrans';
import { Business } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface DirectoryProps {
    businesses: {
        data: Business[];
        links: PaginationLink[];
    };
}

export default function Directory({ businesses }: DirectoryProps) {
    const { t } = useTrans();

    return (
        <>
            <Head title={t('directory.title')} />

            <div className="min-h-screen bg-gray-50">
                <header className="border-b border-gray-200 bg-white">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                        <Link href="/" className="flex items-center gap-2">
                            <span className="text-2xl">📅</span>
                            <span className="text-xl font-bold text-indigo-600">TimeGrid</span>
                        </Link>
                        <LanguageSwitcher />
                        <Link
                            href={route('home')}
                            className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                        >
                            ← {t('directory.back_home')}
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">{t('directory.title')}</h1>
                        <p className="mt-2 text-gray-500">
                            {t('directory.subtitle')}
                        </p>
                    </div>

                    {businesses.data.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                            <p className="text-gray-500">{t('directory.no_businesses')}</p>
                        </div>
                    ) : (
                        <>
                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {businesses.data.map((business) => (
                                    <div
                                        key={business.id}
                                        className="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-indigo-200 hover:shadow-md"
                                    >
                                        <div className="flex-1">
                                            {business.category && (
                                                <span className="inline-block rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                                    {business.category}
                                                </span>
                                            )}
                                            <h2 className="mt-2 text-lg font-semibold text-gray-900">
                                                {business.name}
                                            </h2>
                                            {business.description && (
                                                <p className="mt-2 line-clamp-3 text-sm text-gray-500">
                                                    {business.description}
                                                </p>
                                            )}
                                            {business.services_count !== undefined && (
                                                <p className="mt-3 text-xs text-gray-400">
                                                    {business.services_count} service
                                                    {business.services_count !== 1 ? 's' : ''}
                                                </p>
                                            )}
                                        </div>
                                        <Link
                                            href={`/book/${business.slug}`}
                                            className="mt-4 inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                        >
                                            {t('welcome.book_now')}
                                        </Link>
                                    </div>
                                ))}
                            </div>

                            {businesses.links.length > 3 && (
                                <nav className="mt-10 flex flex-wrap justify-center gap-1">
                                    {businesses.links.map((link, index) =>
                                        link.url ? (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                className={`rounded-md px-3 py-2 text-sm font-medium transition ${
                                                    link.active
                                                        ? 'bg-indigo-600 text-white'
                                                        : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : (
                                            <span
                                                key={index}
                                                className="rounded-md px-3 py-2 text-sm text-gray-400"
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ),
                                    )}
                                </nav>
                            )}
                        </>
                    )}
                </main>
            </div>
        </>
    );
}
