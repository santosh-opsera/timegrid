import { Card, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { ProfileEditPageProps } from '@/types/global';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function ProfileEdit({ mustVerifyEmail, status }: ProfileEditPageProps) {
    const route = useRoute();
    const { auth } = usePage().props;
    const user = auth.user!;

    const profileForm = useForm({
        name: user.name,
        email: user.email,
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submitProfile = (e: FormEvent) => {
        e.preventDefault();
        profileForm.post(route('user.preferences') as string);
    };

    const submitPassword = (e: FormEvent) => {
        e.preventDefault();
        passwordForm.put(route('user.preferences') as string, {
            onSuccess: () => passwordForm.reset(),
        });
    };

    return (
        <AuthenticatedLayout
            title="Profile"
            breadcrumbs={[{ label: 'Profile Settings' }]}
        >
            <Head title="Profile Settings" />
            <PageHeader
                title="Profile Settings"
                description="Manage your account information and security"
            />

            <div className="mx-auto max-w-2xl space-y-8">
                <Card>
                    <h2 className="mb-6 text-lg font-semibold text-slate-900 dark:text-white">Profile information</h2>
                    <form onSubmit={submitProfile} className="space-y-5">
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
                            <input
                                id="name"
                                type="text"
                                value={profileForm.data.name}
                                onChange={(e) => profileForm.setData('name', e.target.value)}
                                className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                required
                            />
                            {profileForm.errors.name && <p className="mt-1.5 text-sm text-red-600" role="alert">{profileForm.errors.name}</p>}
                        </div>
                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                            <input
                                id="email"
                                type="email"
                                value={profileForm.data.email}
                                onChange={(e) => profileForm.setData('email', e.target.value)}
                                className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                required
                            />
                            {profileForm.errors.email && <p className="mt-1.5 text-sm text-red-600" role="alert">{profileForm.errors.email}</p>}
                            {mustVerifyEmail && user.email_verified_at === null && (
                                <p className="mt-2 text-sm text-amber-600">
                                    Your email address is unverified.
                                    {status === 'verification-link-sent' && ' A new verification link has been sent.'}
                                </p>
                            )}
                        </div>
                        <button
                            type="submit"
                            disabled={profileForm.processing}
                            className="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 disabled:opacity-50"
                        >
                            Save changes
                        </button>
                    </form>
                </Card>

                <Card>
                    <h2 className="mb-6 text-lg font-semibold text-slate-900 dark:text-white">Update password</h2>
                    <form onSubmit={submitPassword} className="space-y-5">
                        <div>
                            <label htmlFor="current_password" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Current password</label>
                            <input
                                id="current_password"
                                type="password"
                                value={passwordForm.data.current_password}
                                onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                autoComplete="current-password"
                            />
                            {passwordForm.errors.current_password && <p className="mt-1.5 text-sm text-red-600" role="alert">{passwordForm.errors.current_password}</p>}
                        </div>
                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-slate-700 dark:text-slate-300">New password</label>
                            <input
                                id="password"
                                type="password"
                                value={passwordForm.data.password}
                                onChange={(e) => passwordForm.setData('password', e.target.value)}
                                className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                autoComplete="new-password"
                            />
                            {passwordForm.errors.password && <p className="mt-1.5 text-sm text-red-600" role="alert">{passwordForm.errors.password}</p>}
                        </div>
                        <div>
                            <label htmlFor="password_confirmation" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm password</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                value={passwordForm.data.password_confirmation}
                                onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                className="mt-1.5 block w-full rounded-xl border border-slate-300 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                autoComplete="new-password"
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={passwordForm.processing}
                            className="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 disabled:opacity-50"
                        >
                            Update password
                        </button>
                    </form>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
