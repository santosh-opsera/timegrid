import { usePage } from '@inertiajs/react';

export function useTrans() {
    const props = usePage().props as Record<string, unknown>;
    const locale = (props.locale as string) ?? 'en';
    const translations = (props.translations as Record<string, string>) ?? {};
    const available_locales = (props.available_locales as Record<string, string>) ?? {};

    function t(key: string, replacements?: Record<string, string | number>): string {
        let value = translations[key] ?? key;

        if (replacements) {
            for (const [placeholder, replacement] of Object.entries(replacements)) {
                value = value.replace(new RegExp(`\\{${placeholder}\\}`, 'g'), String(replacement));
            }
        }

        return value;
    }

    return { t, locale, available_locales };
}
