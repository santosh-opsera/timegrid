import { AppointmentStatus } from '@/types';

interface StatusBadgeProps {
    status: AppointmentStatus | string;
    className?: string;
}

const statusConfig: Record<
    string,
    { label: string; classes: string }
> = {
    r: {
        label: 'Reserved',
        classes:
            'bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-950/50 dark:text-brand-400',
    },
    reserved: {
        label: 'Reserved',
        classes:
            'bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-950/50 dark:text-brand-400',
    },
    c: {
        label: 'Confirmed',
        classes:
            'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-400',
    },
    confirmed: {
        label: 'Confirmed',
        classes:
            'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-400',
    },
    a: {
        label: 'Annulled',
        classes:
            'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-950/50 dark:text-red-400',
    },
    pending: {
        label: 'Pending',
        classes:
            'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/50 dark:text-amber-400',
    },
    cancelled: {
        label: 'Cancelled',
        classes:
            'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-950/50 dark:text-red-400',
    },
    completed: {
        label: 'Completed',
        classes:
            'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-950/50 dark:text-blue-400',
    },
    s: {
        label: 'Served',
        classes:
            'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-950/50 dark:text-blue-400',
    },
    'no-show': {
        label: 'No Show',
        classes:
            'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400',
    },
};

export default function StatusBadge({ status, className = '' }: StatusBadgeProps) {
    const normalized = status.toLowerCase();
    const config = statusConfig[normalized] ?? {
        label: status,
        classes:
            'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400',
    };

    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${config.classes} ${className}`}
            role="status"
            aria-label={`Status: ${config.label}`}
        >
            {config.label}
        </span>
    );
}
