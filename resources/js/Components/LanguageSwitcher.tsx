import { useTrans } from '@/hooks/useTrans';
import { router } from '@inertiajs/react';
import { useState } from 'react';

const FLAG_MAP: Record<string, string> = {
    en: '🇬🇧',
    es: '🇪🇸',
    fr: '🇫🇷',
    it: '🇮🇹',
};

export default function LanguageSwitcher({ className = '' }: { className?: string }) {
    const { locale, available_locales } = useTrans();
    const [open, setOpen] = useState(false);

    const switchLocale = (code: string) => {
        setOpen(false);
        window.location.href = `/lang/${code}`;
    };

    return (
        <div className={`relative ${className}`}>
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm text-gray-600 transition hover:text-gray-800 focus:outline-none"
            >
                <span>{FLAG_MAP[locale] ?? '🌐'}</span>
                <span className="hidden sm:inline">{available_locales[locale]}</span>
                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {open && (
                <>
                    <div className="fixed inset-0 z-40" onClick={() => setOpen(false)} />
                    <div className="absolute right-0 z-50 mt-1 w-40 rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                        {Object.entries(available_locales).map(([code, label]) => (
                            <button
                                key={code}
                                onClick={() => switchLocale(code)}
                                className={`flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition hover:bg-gray-100 ${
                                    code === locale ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-gray-700'
                                }`}
                            >
                                <span>{FLAG_MAP[code] ?? '🌐'}</span>
                                <span>{label}</span>
                            </button>
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
