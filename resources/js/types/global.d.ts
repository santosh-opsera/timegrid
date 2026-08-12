import { PageProps as InertiaPageProps } from '@inertiajs/core';

import {
    Appointment,
    Business,
    Contact,
    Notification,
    User,
} from './index';

export interface Auth {
    user: User | null;
}

export interface Flash {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export interface SharedProps {
    auth: Auth;
    flash: Flash;
    locale: string;
    translations: Record<string, string | Record<string, string>>;
    ziggy?: Record<string, unknown>;
    appName: string;
    errors: Record<string, string>;
    notifications?: Notification[];
    selectedBusiness?: Business | null;
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, SharedProps {}
}

declare global {
    function route(
        name?: string,
        params?: Record<string, unknown>,
        absolute?: boolean,
        config?: Record<string, unknown>,
    ): string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = SharedProps & T;

export interface DashboardPageProps {
    appointments?: Appointment[];
    appointmentsCount?: number;
    subscriptionsCount?: number;
}

export interface DirectoryPageProps {
    businesses: Business[];
}

export interface BusinessShowPageProps {
    business: Business;
    available?: boolean;
    appointment?: Appointment | null;
    notifications?: Notification[];
    boxes?: Array<{
        label: string;
        value: string | number;
        icon?: string;
    }>;
    time?: string;
}

export interface BookingCreatePageProps {
    business: Business;
    availability: Record<string, string[]>;
    startFromDate: string;
    endDate: string;
    contact?: Contact | null;
    language?: string;
}

export interface AppointmentsIndexPageProps {
    appointments: Appointment[];
}

export interface ManagerCalendarPageProps {
    business: Business;
    icalURL: string;
    calendarData: {
        minTime: string;
        maxTime: string;
        events: Array<{
            title: string;
            color?: string;
            start: string;
            end: string;
        }>;
        lang: string;
        slotDuration: string;
    };
}

export interface ManagerServicesPageProps {
    business: Business;
}

export interface ManagerContactsPageProps {
    business: Business;
    contacts: Contact[];
}

export interface ManagerStaffPageProps {
    business: Business;
    humanresources?: import('./index').HumanResource[];
}

export interface ManagerVacanciesPageProps {
    business: Business;
    vacancies?: import('./index').Vacancy[];
}

export interface ProfileEditPageProps {
    mustVerifyEmail?: boolean;
    status?: string;
}

export interface BookingShowPageProps {
    business?: Business;
    appointment?: Appointment;
    available?: boolean;
    availability?: Record<string, string[]>;
}
