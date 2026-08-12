import { PropsWithChildren } from 'react';

export function ThemeToggle({ className = '' }: { className?: string }) {
    const toggle = () => {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    };

    return (
        <button
            type="button"
            onClick={toggle}
            className={`rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200 ${className}`}
            aria-label="Toggle dark mode"
        >
            <svg className="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg className="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    );
}

export function Breadcrumbs({ items }: { items: Array<{ label: string; href?: string }> }) {
    if (items.length === 0) return null;

    return (
        <nav aria-label="Breadcrumb" className="mb-6">
            <ol className="flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                {items.map((item, index) => (
                    <li key={`${item.label}-${index}`} className="flex items-center gap-2">
                        {index > 0 && (
                            <span aria-hidden="true" className="text-slate-300 dark:text-slate-600">/</span>
                        )}
                        {item.href && index < items.length - 1 ? (
                            <a href={item.href} className="transition hover:text-brand-600 dark:hover:text-brand-400">
                                {item.label}
                            </a>
                        ) : (
                            <span
                                className={index === items.length - 1 ? 'font-medium text-slate-900 dark:text-white' : ''}
                                aria-current={index === items.length - 1 ? 'page' : undefined}
                            >
                                {item.label}
                            </span>
                        )}
                    </li>
                ))}
            </ol>
        </nav>
    );
}

export function PageHeader({
    title,
    description,
    action,
}: {
    title: string;
    description?: string;
    action?: React.ReactNode;
}) {
    return (
        <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{title}</h1>
                {description && <p className="mt-1 text-slate-500 dark:text-slate-400">{description}</p>}
            </div>
            {action && <div className="shrink-0">{action}</div>}
        </div>
    );
}

export function Card({
    children,
    className = '',
    padding = true,
}: PropsWithChildren<{ className?: string; padding?: boolean }>) {
    return (
        <div className={`rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 ${padding ? 'p-6' : ''} ${className}`}>
            {children}
        </div>
    );
}

export function StatCard({
    label,
    value,
    icon,
    trend,
    color = 'brand',
}: {
    label: string;
    value: string | number;
    icon: React.ReactNode;
    trend?: string;
    color?: 'brand' | 'emerald' | 'amber' | 'blue';
}) {
    const colors = {
        brand: 'from-brand-500 to-blue-500',
        emerald: 'from-emerald-500 to-teal-500',
        amber: 'from-amber-500 to-orange-500',
        blue: 'from-blue-500 to-cyan-500',
    };

    return (
        <Card>
            <div className="flex items-start justify-between">
                <div>
                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{label}</p>
                    <p className="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{value}</p>
                    {trend && <p className="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{trend}</p>}
                </div>
                <div className={`flex h-12 w-12 items-center justify-center rounded-xl bg-linear-to-br ${colors[color]} text-white shadow-lg`}>
                    {icon}
                </div>
            </div>
        </Card>
    );
}

export function EmptyState({
    title,
    description,
    action,
}: {
    title: string;
    description?: string;
    action?: React.ReactNode;
}) {
    return (
        <div className="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-16 text-center dark:border-slate-700">
            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                <svg className="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{title}</h3>
            {description && <p className="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">{description}</p>}
            {action && <div className="mt-6">{action}</div>}
        </div>
    );
}
