import LanguageSwitcher from '@/Components/LanguageSwitcher';
import { useTrans } from '@/hooks/useTrans';
import { Business, User } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface WelcomeProps {
    auth: { user: User | null };
    businesses: Business[];
}

export default function Welcome({ auth, businesses }: WelcomeProps) {
    const { t } = useTrans();

    return (
        <>
            <Head title="TimeGrid — Book appointments online" />

            <div className="min-h-screen bg-gradient-to-b from-indigo-50 to-white">
                <header className="border-b border-indigo-100 bg-white/80 backdrop-blur">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                        <Link href="/" className="flex items-center gap-2">
                            <span className="text-2xl">📅</span>
                            <span className="text-xl font-bold text-indigo-600">TimeGrid</span>
                        </Link>

                        <nav className="flex items-center gap-3">
                            <LanguageSwitcher />
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                >
                                    {t('nav.dashboard')}
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="rounded-lg px-4 py-2 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50"
                                    >
                                        {t('nav.log_in')}
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                                    >
                                        {t('nav.register')}
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <section className="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8 lg:py-24">
                    <div className="mx-auto max-w-3xl">
                        <div className="mb-6 inline-flex items-center justify-center rounded-2xl bg-indigo-100 p-4 text-4xl">
                            📅
                        </div>
                        <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                            TimeGrid
                        </h1>
                        <p className="mt-4 text-lg text-gray-600 sm:text-xl">
                            {t('welcome.tagline')}
                        </p>
                        <p className="mt-6 text-base text-gray-500">
                            {t('welcome.subtitle')}
                        </p>
                        <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            <Link
                                href={route('directory')}
                                className="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                            >
                                {t('welcome.browse_all')}
                            </Link>
                            {!auth.user && (
                                <Link
                                    href={route('register')}
                                    className="rounded-lg border border-indigo-200 bg-white px-6 py-3 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50"
                                >
                                    {t('welcome.get_started')}
                                </Link>
                            )}
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
                    <div className="mb-8 flex items-end justify-between">
                        <div>
                            <h2 className="text-2xl font-bold text-gray-900">{t('welcome.featured')}</h2>
                            <p className="mt-1 text-gray-500">{t('welcome.featured_subtitle')}</p>
                        </div>
                        <Link
                            href={route('directory')}
                            className="hidden text-sm font-medium text-indigo-600 hover:text-indigo-700 sm:block"
                        >
                            {`${t('welcome.view_all')} →`}
                        </Link>
                    </div>

                    {businesses.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                            <p className="text-gray-500">{t('welcome.no_businesses')}</p>
                        </div>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {businesses.map((business) => (
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
                                        <h3 className="mt-2 text-lg font-semibold text-gray-900">
                                            {business.name}
                                        </h3>
                                        {business.description && (
                                            <p className="mt-2 line-clamp-2 text-sm text-gray-500">
                                                {business.description}
                                            </p>
                                        )}
                                        {business.services_count !== undefined && (
                                            <p className="mt-3 text-xs text-gray-400">
                                                {business.services_count}{' '}
                                                {business.services_count !== 1 ? t('dashboard.services') : 'service'}{' '}
                                                available
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
                    )}
                </section>

                <footer className="border-t border-gray-200 bg-white py-8 text-center text-sm text-gray-500">
                    © {new Date().getFullYear()} TimeGrid. {t('welcome.footer')}
                </footer>
            </div>
        </>
    );
}
