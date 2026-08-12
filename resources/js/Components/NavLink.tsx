import { InertiaLinkProps, Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

interface NavLinkProps extends InertiaLinkProps {
    active?: boolean;
}

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}: PropsWithChildren<NavLinkProps>) {
    const baseClasses =
        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200';

    const activeClasses = active
        ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/25'
        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white';

    return (
        <Link
            {...props}
            className={`${baseClasses} ${activeClasses} ${className}`}
            aria-current={active ? 'page' : undefined}
        >
            {children}
        </Link>
    );
}
