import GuestLayout from '@/Layouts/GuestLayout';
import useRoute from '@/Hooks/useRoute';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function Register() {
    const route = useRoute();
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'customer' as 'customer' | 'business',
        allow_register: true,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('register') as string);
    };

    return (
        <GuestLayout>
            <Head title="Register" />

            <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-4 py-12">
                <div className="w-full max-w-md">
                    <div className="mb-8 text-center">
                        <h1 className="text-3xl font-bold text-slate-900 dark:text-white">Create your account</h1>
                        <p className="mt-2 text-slate-600 dark:text-slate-400">
                            Join Timegrid and start booking in minutes
                        </p>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Full name
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required
                                    autoComplete="name"
                                />
                                {errors.name && <p className="mt-1.5 text-sm text-red-600" role="alert">{errors.name}</p>}
                            </div>

                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Email address
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required
                                    autoComplete="email"
                                />
                                {errors.email && <p className="mt-1.5 text-sm text-red-600" role="alert">{errors.email}</p>}
                            </div>

                            <div>
                                <label htmlFor="password" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Password
                                </label>
                                <input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required
                                    autoComplete="new-password"
                                />
                                {errors.password && <p className="mt-1.5 text-sm text-red-600" role="alert">{errors.password}</p>}
                            </div>

                            <div>
                                <label htmlFor="password_confirmation" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Confirm password
                                </label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 shadow-sm transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    required
                                    autoComplete="new-password"
                                />
                            </div>

                            <fieldset>
                                <legend className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    I am a…
                                </legend>
                                <div className="mt-3 grid grid-cols-2 gap-3">
                                    {(['customer', 'business'] as const).map((role) => (
                                        <label
                                            key={role}
                                            className={`flex cursor-pointer flex-col items-center rounded-xl border-2 p-4 transition ${
                                                data.role === role
                                                    ? 'border-brand-600 bg-brand-50 dark:bg-brand-950'
                                                    : 'border-slate-200 hover:border-slate-300 dark:border-slate-700'
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                name="role"
                                                value={role}
                                                checked={data.role === role}
                                                onChange={() => setData('role', role)}
                                                className="sr-only"
                                            />
                                            <span className="text-sm font-semibold capitalize text-slate-900 dark:text-white">
                                                {role === 'customer' ? 'Customer' : 'Business Owner'}
                                            </span>
                                            <span className="mt-1 text-xs text-slate-500">
                                                {role === 'customer' ? 'Book appointments' : 'Manage my business'}
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-brand-600 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 disabled:opacity-50"
                            >
                                {processing ? 'Creating account…' : 'Create account'}
                            </button>
                        </form>

                        <p className="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
                            Already have an account?{' '}
                            <Link href={route('login') as string} className="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                Sign in
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
