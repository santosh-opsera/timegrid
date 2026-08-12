import { Card, EmptyState, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { DirectoryPageProps } from '@/types/global';
import { Head, Link } from '@inertiajs/react';
import {
    BuildingStorefrontIcon,
    MagnifyingGlassIcon,
    MapPinIcon,
    StarIcon,
} from '@heroicons/react/24/outline';
import { useMemo, useState } from 'react';

export default function Directory({ businesses }: DirectoryPageProps) {
    const route = useRoute();
    const [search, setSearch] = useState('');
    const [category, setCategory] = useState('all');

    const categories = useMemo(() => {
        const cats = businesses
            .map((b) => b.category?.slug)
            .filter(Boolean) as string[];
        return ['all', ...Array.from(new Set(cats))];
    }, [businesses]);

    const filtered = useMemo(() => {
        return businesses.filter((business) => {
            const matchesSearch =
                !search ||
                business.name.toLowerCase().includes(search.toLowerCase()) ||
                business.description?.toLowerCase().includes(search.toLowerCase());
            const matchesCategory =
                category === 'all' || business.category?.slug === category;
            return matchesSearch && matchesCategory;
        });
    }, [businesses, search, category]);

    return (
        <AuthenticatedLayout
            title="Directory"
            breadcrumbs={[{ label: 'Directory' }]}
        >
            <Head title="Directory" />
            <PageHeader
                title="Business Directory"
                description="Discover and book appointments with local businesses"
            />

            <div className="mb-8 flex flex-col gap-4 sm:flex-row">
                <div className="relative flex-1">
                    <MagnifyingGlassIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                    <input
                        type="search"
                        placeholder="Search businesses…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-full rounded-xl border border-slate-300 bg-white py-3 pl-12 pr-4 shadow-sm transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        aria-label="Search businesses"
                    />
                </div>
                <select
                    value={category}
                    onChange={(e) => setCategory(e.target.value)}
                    className="rounded-xl border border-slate-300 bg-white px-4 py-3 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    aria-label="Filter by category"
                >
                    {categories.map((cat) => (
                        <option key={cat} value={cat}>
                            {cat === 'all' ? 'All categories' : cat.replace(/-/g, ' ')}
                        </option>
                    ))}
                </select>
            </div>

            {filtered.length === 0 ? (
                <EmptyState
                    title="No businesses found"
                    description="Try adjusting your search or filter criteria."
                />
            ) : (
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {filtered.map((business) => (
                        <Link
                            key={business.id}
                            href={`/${business.slug}`}
                            className="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-brand-200 hover:shadow-xl hover:shadow-brand-600/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800"
                        >
                            <div className="mb-4 flex items-start justify-between">
                                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-950 dark:text-brand-400">
                                    <BuildingStorefrontIcon className="h-6 w-6" aria-hidden="true" />
                                </div>
                                <div className="flex items-center gap-1 text-amber-500">
                                    <StarIcon className="h-4 w-4 fill-current" aria-hidden="true" />
                                    <span className="text-sm font-medium text-slate-600 dark:text-slate-400">4.8</span>
                                </div>
                            </div>
                            <h3 className="text-lg font-semibold text-slate-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">
                                {business.name}
                            </h3>
                            {business.category && (
                                <span className="mt-1 inline-block rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    {business.category.slug.replace(/-/g, ' ')}
                                </span>
                            )}
                            {business.description && (
                                <p className="mt-3 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">
                                    {business.description}
                                </p>
                            )}
                            <div className="mt-4 flex items-center justify-between text-sm text-slate-500">
                                <span>{business.services?.length ?? 0} services</span>
                                {business.postal_address && (
                                    <span className="flex items-center gap-1 truncate">
                                        <MapPinIcon className="h-4 w-4 shrink-0" aria-hidden="true" />
                                        <span className="truncate">{business.postal_address}</span>
                                    </span>
                                )}
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </AuthenticatedLayout>
    );
}
