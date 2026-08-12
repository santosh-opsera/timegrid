import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export default function FlashMessages() {
    const { flash } = usePage<{ flash: { success?: string; error?: string; warning?: string } }>().props;

    useEffect(() => {
        if (flash?.success || flash?.error || flash?.warning) {
            const timer = setTimeout(() => {}, 5000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    if (!flash?.success && !flash?.error && !flash?.warning) {
        return null;
    }

    return (
        <div className="fixed right-4 top-4 z-50 flex max-w-sm flex-col gap-2" role="alert">
            {flash.success && (
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-lg dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                    {flash.success}
                </div>
            )}
            {flash.warning && (
                <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 shadow-lg dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
                    {flash.warning}
                </div>
            )}
            {flash.error && (
                <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                    {flash.error}
                </div>
            )}
        </div>
    );
}
