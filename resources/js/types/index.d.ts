export interface Category {
    id: number;
    slug: string;
    name?: string;
}

export interface ServiceType {
    id: number;
    name: string;
}

export interface Service {
    id: number;
    name: string;
    price?: number | string;
    duration?: number;
    color?: string;
    description?: string;
    service_type_id?: number;
    type?: ServiceType;
    servicetype?: ServiceType;
}

export interface Business {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    phone?: string | null;
    postal_address?: string | null;
    timezone?: string;
    locale?: string;
    listed?: boolean;
    category_id?: number;
    plan?: string;
    strategy?: string;
    social_facebook?: string | null;
    category?: Category;
    services?: Service[];
    contacts?: Contact[];
    servicetypes?: ServiceType[];
}

export interface Contact {
    id: number;
    firstname: string;
    lastname?: string | null;
    email: string;
    phone?: string | null;
    mobile?: string | null;
    gender?: string | null;
    birthday?: string | null;
    businesses?: Business[];
}

export interface User {
    id: number;
    name: string;
    email: string;
    username?: string;
    email_verified_at?: string | null;
    businesses?: Business[];
}

export interface Role {
    id: number;
    name: string;
    slug: string;
}

export interface Appointment {
    id: number;
    code?: string;
    hash?: string;
    status: AppointmentStatus | string;
    start_at: string;
    finish_at?: string;
    comments?: string | null;
    business_id?: number;
    service_id?: number;
    contact_id?: number;
    business?: Business;
    service?: Service;
    contact?: Contact;
}

export type AppointmentStatus =
    | 'confirmed'
    | 'pending'
    | 'cancelled'
    | 'completed'
    | 'no-show';

export interface Vacancy {
    id: number;
    start_at: string;
    finish_at: string;
    capacity?: number;
    day?: string;
    time?: string;
}

export interface HumanResource {
    id: number;
    name: string;
    email?: string | null;
    phone?: string | null;
    calendar_link?: string | null;
}

export interface AvailabilitySlot {
    date: string;
    times: string[];
}

export interface Availability {
    [date: string]: string[] | AvailabilitySlot;
}

export interface DashboardBox {
    label: string;
    value: string | number;
    icon?: string;
    trend?: string;
    color?: string;
}

export interface Notification {
    id: number;
    category?: string;
    body?: string;
    url?: string;
    created_at?: string;
    read_at?: string | null;
}

export interface CalendarEvent {
    title: string;
    color?: string;
    start: string;
    end: string;
}

export interface CalendarData {
    minTime: string;
    maxTime: string;
    events: CalendarEvent[];
    lang: string;
    slotDuration: string;
}

export interface BreadcrumbItem {
    label: string;
    href?: string;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface ActivityItem {
    id: number | string;
    type: string;
    description: string;
    created_at: string;
    user?: Pick<User, 'id' | 'name'>;
}
