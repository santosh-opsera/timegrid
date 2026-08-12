import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';

type Replacements = Record<string, string | number>;

function resolveKey(
    translations: Record<string, string | Record<string, string>>,
    key: string,
): string | undefined {
    const parts = key.split('.');
    let current: string | Record<string, string> | undefined = translations;

    for (const part of parts) {
        if (typeof current !== 'object' || current === null) {
            return undefined;
        }
        current = current[part] as string | Record<string, string> | undefined;
    }

    return typeof current === 'string' ? current : undefined;
}

function applyReplacements(text: string, replace?: Replacements): string {
    if (!replace) {
        return text;
    }

    return Object.entries(replace).reduce(
        (result, [key, value]) => result.replace(`:${key}`, String(value)),
        text,
    );
}

export function useTrans() {
    const { translations } = usePage().props;

    const trans = useCallback(
        (key: string, replace?: Replacements): string => {
            const resolved = resolveKey(translations ?? {}, key);
            if (resolved) {
                return applyReplacements(resolved, replace);
            }
            return applyReplacements(key, replace);
        },
        [translations],
    );

    return { trans, t: trans };
}

export default useTrans;
