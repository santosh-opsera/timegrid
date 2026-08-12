import { Menu, MenuButton, MenuItem, MenuItems, Transition } from '@headlessui/react';
import { ChevronDownIcon, LanguageIcon } from '@heroicons/react/24/outline';
import { router } from '@inertiajs/react';
import { Fragment } from 'react';

import useRoute from '@/Hooks/useRoute';

const languages = [
    { code: 'en_US', label: 'English', flag: '🇺🇸' },
    { code: 'es_ES', label: 'Español', flag: '🇪🇸' },
    { code: 'fr_FR', label: 'Français', flag: '🇫🇷' },
    { code: 'it_IT', label: 'Italiano', flag: '🇮🇹' },
    { code: 'ru_RU', label: 'Русский', flag: '🇷🇺' },
];

interface LanguageSwitcherProps {
    currentLocale?: string;
    className?: string;
}

export default function LanguageSwitcher({
    currentLocale = 'en_US',
    className = '',
}: LanguageSwitcherProps) {
    const route = useRoute();
    const current =
        languages.find((l) => l.code === currentLocale) ?? languages[0];

    const switchLanguage = (code: string) => {
        router.visit(route('lang.switch', { lang: code }), {
            preserveScroll: true,
        });
    };

    return (
        <Menu as="div" className={`relative inline-block text-left ${className}`}>
            <MenuButton
                className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                aria-label="Select language"
            >
                <LanguageIcon className="h-4 w-4 text-slate-400" aria-hidden="true" />
                <span>{current.flag}</span>
                <span className="hidden sm:inline">{current.label}</span>
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
                <MenuItems className="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl border border-slate-200 bg-white p-1 shadow-xl focus:outline-none dark:border-slate-700 dark:bg-slate-800">
                    {languages.map((lang) => (
                        <MenuItem key={lang.code}>
                            {({ focus }) => (
                                <button
                                    type="button"
                                    onClick={() => switchLanguage(lang.code)}
                                    className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm ${
                                        focus
                                            ? 'bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300'
                                            : 'text-slate-700 dark:text-slate-200'
                                    } ${lang.code === currentLocale ? 'font-semibold' : ''}`}
                                >
                                    <span>{lang.flag}</span>
                                    {lang.label}
                                </button>
                            )}
                        </MenuItem>
                    ))}
                </MenuItems>
            </Transition>
        </Menu>
    );
}
