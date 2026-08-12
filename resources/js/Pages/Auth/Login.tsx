import GuestLayout from '@/Layouts/GuestLayout';
import useRoute from '@/Hooks/useRoute';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function Login() {
    const route = useRoute();
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('login') as string);
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-4 py-12">
                <div className="w-full max-w-md">
                    <div className="mb-8 text-center">
                        <h1 className="text-3xl font-bold text-slate-900 dark:text-white">Welcome back</h1>
                        <p className="mt-2 text-slate-600 dark:text-slate-400">
                            Sign in to manage your appointments
                        </p>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Email address
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    autoComplete="email"
                                    required
                                    aria-invalid={!!errors.email}
                                    aria-describedby={errors.email ? 'email-error' : undefined}
                                />
                                {errors.email && (
                                    <p id="email-error" className="mt-1.5 text-sm text-red-600" role="alert">{errors.email}</p>
                                )}
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
                                    className="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                    autoComplete="current-password"
                                    required
                                    aria-invalid={!!errors.password}
                                />
                                {errors.password && (
                                    <p className="mt-1.5 text-sm text-red-600" role="alert">{errors.password}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-between">
                                <label className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', e.target.checked)}
                                        className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    />
                                    <span className="text-sm text-slate-600 dark:text-slate-400">Remember me</span>
                                </label>
                                <Link
                                    href={route('password.request') as string}
                                    className="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                                >
                                    Forgot password?
                                </Link>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-brand-600 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 disabled:opacity-50"
                            >
                                {processing ? 'Signing in…' : 'Sign in'}
                            </button>
                        </form>

                        <p className="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
                            Don&apos;t have an account?{' '}
                            <Link href={route('register') as string} className="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                Register
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
