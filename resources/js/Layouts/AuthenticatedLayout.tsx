import {
    Bars3Icon,
    BellIcon,
    BuildingStorefrontIcon,
    CalendarDaysIcon,
    ChevronDownIcon,
    HomeIcon,
    UserCircleIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { Link, usePage } from '@inertiajs/react';
import { Menu, MenuButton, MenuItem, MenuItems, Transition } from '@headlessui/react';
import { Fragment, PropsWithChildren, useEffect, useState } from 'react';

import ApplicationLogo from '@/Components/ApplicationLogo';
import FlashMessages from '@/Components/Flash';
import LanguageSwitcher from '@/Components/LanguageSwitcher';
import NavLink from '@/Components/NavLink';
import { Breadcrumbs, ThemeToggle } from '@/Components/UI';
import useRoute from '@/Hooks/useRoute';
import { BreadcrumbItem } from '@/types';

interface AuthenticatedLayoutProps extends PropsWithChildren {
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
}

export default function AuthenticatedLayout({
    children,
    breadcrumbs = [],
    title,
}: AuthenticatedLayoutProps) {
    const route = useRoute();
    const { auth, locale, notifications = [] } = usePage().props;
    const user = auth.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    useEffect(() => {
        const stored = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', stored === 'dark' || (!stored && prefersDark));
    }, []);

    const navigation = [
        { name: 'Dashboard', href: route('user.dashboard') as string, icon: HomeIcon, routeName: 'user.dashboard' },
        { name: 'My Appointments', href: route('user.agenda') as string, icon: CalendarDaysIcon, routeName: 'user.agenda' },
        { name: 'Directory', href: route('user.directory.list') as string, icon: BuildingStorefrontIcon, routeName: 'user.directory.list' },
        { name: 'Profile', href: route('user.preferences') as string, icon: UserCircleIcon, routeName: 'user.preferences' },
    ];

    const routeHelper = route() as { current: (name?: string) => boolean };

    const unreadCount = notifications?.length ?? 0;

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-950">
            <FlashMessages />

            {/* Mobile sidebar overlay */}
            <Transition show={sidebarOpen} as={Fragment}>
                <div className="relative z-50 lg:hidden">
                    <Transition.Child
                        as={Fragment}
                        enter="transition-opacity ease-linear duration-300"
                        enterFrom="opacity-0"
                        enterTo="opacity-100"
                        leave="transition-opacity ease-linear duration-300"
                        leaveFrom="opacity-100"
                        leaveTo="opacity-0"
                    >
                        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={() => setSidebarOpen(false)} />
                    </Transition.Child>

                    <Transition.Child
                        as={Fragment}
                        enter="transition ease-in-out duration-300 transform"
                        enterFrom="-translate-x-full"
                        enterTo="translate-x-0"
                        leave="transition ease-in-out duration-300 transform"
                        leaveFrom="translate-x-0"
                        leaveTo="-translate-x-full"
                    >
                        <div className="fixed inset-y-0 left-0 flex w-72 flex-col bg-white shadow-2xl dark:bg-slate-900">
                            <div className="flex h-16 items-center justify-between px-6">
                                <Link href={route('welcome')} className="flex items-center gap-3">
                                    <ApplicationLogo className="h-9 w-9" />
                                    <span className="text-lg font-bold text-slate-900 dark:text-white">Timegrid</span>
                                </Link>
                                <button type="button" onClick={() => setSidebarOpen(false)} className="rounded-lg p-2 text-slate-400 hover:bg-slate-100" aria-label="Close menu">
                                    <XMarkIcon className="h-6 w-6" />
                                </button>
                            </div>
                            <nav className="flex-1 space-y-1 px-4 py-4" aria-label="Main navigation">
                                {navigation.map((item) => (
                                    <NavLink key={item.name} href={item.href} active={routeHelper.current(item.routeName)}>
                                        <item.icon className="h-5 w-5 shrink-0" aria-hidden="true" />
                                        {item.name}
                                    </NavLink>
                                ))}
                            </nav>
                        </div>
                    </Transition.Child>
                </div>
            </Transition>

            {/* Desktop sidebar */}
            <aside className="fixed inset-y-0 z-40 hidden w-64 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 lg:flex">
                <div className="flex h-16 items-center gap-3 border-b border-slate-200 px-6 dark:border-slate-800">
                    <Link href={route('welcome')} className="flex items-center gap-3">
                        <ApplicationLogo className="h-9 w-9" />
                        <span className="text-lg font-bold text-slate-900 dark:text-white">Timegrid</span>
                    </Link>
                </div>
                <nav className="flex-1 space-y-1 px-4 py-6" aria-label="Main navigation">
                    {navigation.map((item) => (
                        <NavLink key={item.name} href={item.href} active={routeHelper.current(item.routeName)}>
                            <item.icon className="h-5 w-5 shrink-0" aria-hidden="true" />
                            {item.name}
                        </NavLink>
                    ))}
                </nav>
                {user && (
                    <div className="border-t border-slate-200 p-4 dark:border-slate-800">
                        <div className="flex items-center gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700 dark:bg-brand-950 dark:text-brand-300">
                                {user.name.charAt(0).toUpperCase()}
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium text-slate-900 dark:text-white">{user.name}</p>
                                <p className="truncate text-xs text-slate-500">{user.email}</p>
                            </div>
                        </div>
                    </div>
                )}
            </aside>

            {/* Main content */}
            <div className="lg:pl-64">
                <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                    <div className="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-4">
                            <button
                                type="button"
                                className="rounded-xl p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800"
                                onClick={() => setSidebarOpen(true)}
                                aria-label="Open menu"
                            >
                                <Bars3Icon className="h-6 w-6" />
                            </button>
                            {title && (
                                <h1 className="text-lg font-semibold text-slate-900 dark:text-white lg:hidden">{title}</h1>
                            )}
                        </div>

                        <div className="flex items-center gap-2 sm:gap-3">
                            <ThemeToggle />
                            <LanguageSwitcher currentLocale={locale} />

                            <Link
                                href={route('user.agenda')}
                                className="relative rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                aria-label={`Notifications${unreadCount > 0 ? `, ${unreadCount} unread` : ''}`}
                            >
                                <BellIcon className="h-5 w-5" aria-hidden="true" />
                                {unreadCount > 0 && (
                                    <span className="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                        {unreadCount > 9 ? '9+' : unreadCount}
                                    </span>
                                )}
                            </Link>

                            <Menu as="div" className="relative">
                                <MenuButton className="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                                    <div className="flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white">
                                        {user?.name.charAt(0).toUpperCase()}
                                    </div>
                                    <span className="hidden sm:inline">{user?.name}</span>
                                    <ChevronDownIcon className="h-4 w-4 text-slate-400" aria-hidden="true" />
                                </MenuButton>
                                <Transition
                                    as={Fragment}
                                    enter="transition ease-out duration-100"
                                    enterFrom="transform opacity-0 scale-95"
                                    enterTo="transform opacity-100 scale-100"
                                    leave="transition ease-in duration-75"
                                    leaveFrom="transform opacity-100 scale-100"
                                    leaveTo="transform opacity-0 scale-95"
                                >
                                    <MenuItems className="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl border border-slate-200 bg-white p-1 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                                        <MenuItem>
                                            {({ focus }) => (
                                                <Link
                                                    href={route('user.preferences')}
                                                    className={`block rounded-lg px-3 py-2 text-sm ${focus ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200'}`}
                                                >
                                                    Profile Settings
                                                </Link>
                                            )}
                                        </MenuItem>
                                        <MenuItem>
                                            {({ focus }) => (
                                                <Link
                                                    href={route('logout')}
                                                    method="get"
                                                    as="button"
                                                    className={`block w-full rounded-lg px-3 py-2 text-left text-sm ${focus ? 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300' : 'text-slate-700 dark:text-slate-200'}`}
                                                >
                                                    Sign out
                                                </Link>
                                            )}
                                        </MenuItem>
                                    </MenuItems>
                                </Transition>
                            </Menu>
                        </div>
                    </div>
                </header>

                <main className="px-4 py-8 sm:px-6 lg:px-8">
                    {breadcrumbs.length > 0 && <Breadcrumbs items={breadcrumbs} />}
                    {children}
                </main>
            </div>
        </div>
    );
}
