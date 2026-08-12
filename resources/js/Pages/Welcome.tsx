import ApplicationLogo from '@/Components/ApplicationLogo';
import GuestLayout from '@/Layouts/GuestLayout';
import useRoute from '@/Hooks/useRoute';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowRightIcon,
    CalendarDaysIcon,
    ChartBarIcon,
    ClockIcon,
    SparklesIcon,
    UserGroupIcon,
} from '@heroicons/react/24/outline';

const features = [
    {
        icon: CalendarDaysIcon,
        title: 'Smart Scheduling',
        description: 'Intelligent availability management with real-time slot detection and conflict prevention.',
    },
    {
        icon: UserGroupIcon,
        title: 'Multi-Business Directory',
        description: 'Discover and book appointments across a curated directory of local service providers.',
    },
    {
        icon: ClockIcon,
        title: '24/7 Self-Service',
        description: 'Let customers book anytime from any device. Reduce no-shows with automated reminders.',
    },
    {
        icon: ChartBarIcon,
        title: 'Business Insights',
        description: 'Track appointments, manage staff, and grow your business with powerful manager tools.',
    },
];

const steps = [
    { step: '01', title: 'Create your account', description: 'Sign up as a customer or register your business in minutes.' },
    { step: '02', title: 'Find or list services', description: 'Browse the directory or set up your services and availability.' },
    { step: '03', title: 'Book & manage', description: 'Customers book instantly. You manage everything from one dashboard.' },
];

export default function Welcome() {
    const route = useRoute();

    return (
        <GuestLayout>
            <Head title="Welcome" />

            {/* Hero */}
            <section className="relative overflow-hidden">
                <div className="absolute inset-0 -z-10">
                    <div className="absolute inset-0 bg-linear-to-br from-brand-50 via-white to-blue-50 dark:from-brand-950 dark:via-slate-950 dark:to-slate-900" />
                    <div className="absolute -top-40 right-0 h-96 w-96 rounded-full bg-brand-400/20 blur-3xl" />
                    <div className="absolute -bottom-40 left-0 h-96 w-96 rounded-full bg-blue-400/20 blur-3xl" />
                </div>

                <div className="mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
                    <div className="mx-auto max-w-3xl text-center">
                        <div className="mb-8 inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-1.5 text-sm font-medium text-brand-700 dark:border-brand-800 dark:bg-brand-950 dark:text-brand-300">
                            <SparklesIcon className="h-4 w-4" aria-hidden="true" />
                            Open-source appointment platform
                        </div>
                        <h1 className="text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl dark:text-white">
                            Scheduling made{' '}
                            <span className="text-gradient">effortless</span>
                        </h1>
                        <p className="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">
                            Timegrid connects customers with businesses through beautiful, frictionless booking.
                            Manage appointments, grow your client base, and never miss a slot again.
                        </p>
                        <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            <Link
                                href={route('register') as string}
                                className="inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-8 py-3.5 text-base font-semibold text-white shadow-xl shadow-brand-600/30 transition hover:bg-brand-700 hover:shadow-brand-600/40"
                            >
                                Start for free
                                <ArrowRightIcon className="h-5 w-5" aria-hidden="true" />
                            </Link>
                            <Link
                                href={route('user.directory.list') as string}
                                className="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-8 py-3.5 text-base font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                Browse directory
                            </Link>
                        </div>
                    </div>

                    <div className="mx-auto mt-16 max-w-5xl">
                        <div className="rounded-2xl border border-slate-200/80 bg-white/60 p-2 shadow-2xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/60">
                            <div className="rounded-xl bg-slate-900 p-6 dark:bg-slate-950">
                                <div className="mb-4 flex items-center gap-2">
                                    <div className="h-3 w-3 rounded-full bg-red-400" />
                                    <div className="h-3 w-3 rounded-full bg-amber-400" />
                                    <div className="h-3 w-3 rounded-full bg-emerald-400" />
                                </div>
                                <div className="grid gap-4 sm:grid-cols-3">
                                    {['Today\'s Appointments', 'Upcoming', 'Revenue'].map((label, i) => (
                                        <div key={label} className="rounded-xl bg-slate-800 p-4">
                                            <p className="text-xs text-slate-400">{label}</p>
                                            <p className="mt-1 text-2xl font-bold text-white">{[12, 8, '$2.4k'][i]}</p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Features */}
            <section id="features" className="py-24 sm:py-32">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                            Everything you need to book smarter
                        </h2>
                        <p className="mt-4 text-lg text-slate-600 dark:text-slate-400">
                            Powerful features for businesses and a seamless experience for customers.
                        </p>
                    </div>
                    <div className="mx-auto mt-16 grid max-w-5xl gap-8 sm:grid-cols-2">
                        {features.map((feature) => (
                            <div
                                key={feature.title}
                                className="group rounded-2xl border border-slate-200 bg-white p-8 transition hover:border-brand-200 hover:shadow-xl hover:shadow-brand-600/5 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-800"
                            >
                                <div className="mb-4 inline-flex rounded-xl bg-brand-50 p-3 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-950 dark:text-brand-400">
                                    <feature.icon className="h-6 w-6" aria-hidden="true" />
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{feature.title}</h3>
                                <p className="mt-2 text-slate-600 dark:text-slate-400">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* How it works */}
            <section id="how-it-works" className="bg-slate-100 py-24 dark:bg-slate-900/50 sm:py-32">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-2xl text-center">
                        <h2 className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                            How it works
                        </h2>
                        <p className="mt-4 text-lg text-slate-600 dark:text-slate-400">
                            Get started in three simple steps
                        </p>
                    </div>
                    <div className="mx-auto mt-16 grid max-w-4xl gap-8 md:grid-cols-3">
                        {steps.map((item) => (
                            <div key={item.step} className="relative text-center">
                                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-lg font-bold text-white shadow-lg shadow-brand-600/30">
                                    {item.step}
                                </div>
                                <h3 className="text-lg font-semibold text-slate-900 dark:text-white">{item.title}</h3>
                                <p className="mt-2 text-sm text-slate-600 dark:text-slate-400">{item.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* CTA */}
            <section className="py-24">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="relative overflow-hidden rounded-3xl bg-linear-to-br from-brand-600 via-brand-500 to-blue-500 px-8 py-16 text-center shadow-2xl sm:px-16">
                        <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggIGQ9Ik0zNiAzNGg0djRoLTR6TTAgMzRoNHY0SDB6TTAgMzRoNHY0SDB6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30" />
                        <ApplicationLogo className="mx-auto mb-6 h-16 w-16" />
                        <h2 className="text-3xl font-bold text-white sm:text-4xl">
                            Ready to transform your scheduling?
                        </h2>
                        <p className="mx-auto mt-4 max-w-xl text-lg text-brand-100">
                            Join thousands of businesses and customers who trust Timegrid for their appointments.
                        </p>
                        <Link
                            href={route('register') as string}
                            className="mt-8 inline-flex items-center gap-2 rounded-2xl bg-white px-8 py-3.5 text-base font-semibold text-brand-600 shadow-xl transition hover:bg-brand-50"
                        >
                            Create free account
                            <ArrowRightIcon className="h-5 w-5" aria-hidden="true" />
                        </Link>
                    </div>
                </div>
            </section>
        </GuestLayout>
    );
}
