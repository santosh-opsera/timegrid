export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role: string;
}

export interface Business {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    category: string | null;
    timezone: string;
    strategy: string;
    phone: string | null;
    postal_address: string | null;
    plan: string;
    services_count?: number;
    contacts_count?: number;
    appointments_count?: number;
    services?: Service[];
    staff?: Staff[];
}

export interface Service {
    id: number;
    business_id: number;
    name: string;
    description: string | null;
    duration: number;
    color: string;
    is_active: boolean;
}

export interface Staff {
    id: number;
    business_id: number;
    name: string;
}

export interface Contact {
    id: number;
    business_id: number;
    user_id: number | null;
    firstname: string;
    lastname: string | null;
    email: string | null;
    phone: string | null;
    notes: string | null;
    name?: string;
}

export interface Vacancy {
    id: number;
    business_id: number;
    service_id: number;
    staff_id: number | null;
    date: string;
    start_time: string;
    end_time: string;
    capacity: number;
    service?: Service;
    staff?: Staff;
}

export interface Appointment {
    id: number;
    business_id: number;
    service_id: number;
    contact_id: number;
    staff_id: number | null;
    status: string;
    start_at: string;
    end_at: string;
    duration: number;
    comments: string | null;
    hash: string;
    service?: Service;
    contact?: Contact;
    business?: Business;
    staff?: Staff;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
        can: {
            manage_businesses: boolean;
        };
    };
    flash?: {
        success?: string;
        error?: string;
    };
};
