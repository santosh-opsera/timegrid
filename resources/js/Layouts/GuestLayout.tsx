import ApplicationLogo from '@/Components/ApplicationLogo';
import FlashMessages from '@/Components/Flash';
import LanguageSwitcher from '@/Components/LanguageSwitcher';
import { ThemeToggle } from '@/Components/UI';
import useRoute from '@/Hooks/useRoute';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function GuestLayout({ children }: PropsWithChildren) {
    const route = useRoute();
    const { auth, locale } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-slate-50 dark:bg-slate-950">
            <FlashMessages />

            <header className="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link href={route('welcome') as string} className="flex items-center gap-3 transition hover:opacity-80">
                        <ApplicationLogo className="h-9 w-9" />
                        <span className="text-xl font-bold text-slate-900 dark:text-white">Timegrid</span>
                    </Link>

                    <nav className="hidden items-center gap-8 md:flex" aria-label="Main navigation">
                        <a href="#features" className="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400">
                            Features
                        </a>
                        <a href="#how-it-works" className="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400">
                            How it works
                        </a>
                        <Link href={route('user.directory.list') as string} className="text-sm font-medium text-slate-600 transition hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400">
                            Directory
                        </Link>
                    </nav>

                    <div className="flex items-center gap-2 sm:gap-3">
                        <ThemeToggle />
                        <LanguageSwitcher currentLocale={locale} />
                        {auth.user ? (
                            <Link
                                href={route('user.dashboard') as string}
                                className="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login') as string}
                                    className="hidden rounded-xl px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:inline-block dark:text-slate-200 dark:hover:bg-slate-800"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register') as string}
                                    className="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700"
                                >
                                    Get started
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                    <div className="flex flex-col items-center justify-between gap-6 md:flex-row">
                        <div className="flex items-center gap-3">
                            <ApplicationLogo className="h-8 w-8" />
                            <span className="font-semibold text-slate-900 dark:text-white">Timegrid</span>
                        </div>
                        <p className="text-sm text-slate-500 dark:text-slate-400">
                            © {new Date().getFullYear()} Timegrid. Open-source appointment scheduling.
                        </p>
                        <div className="flex gap-6 text-sm text-slate-500 dark:text-slate-400">
                            <Link href={route('login') as string} className="transition hover:text-brand-600">Login</Link>
                            <Link href={route('register') as string} className="transition hover:text-brand-600">Register</Link>
                            <a href="https://github.com/timegridio/timegrid" className="transition hover:text-brand-600" target="_blank" rel="noopener noreferrer">
                                GitHub
                            </a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}
