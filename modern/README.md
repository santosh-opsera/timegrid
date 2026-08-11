# TimeGrid

Online appointment booking platform. Businesses publish their available time slots, customers browse and book appointments.

Built with **Laravel 11**, **Inertia.js**, **React 19**, **TypeScript**, and **Tailwind CSS**.

---

## Screenshots

### Landing Page
Public homepage with featured businesses and a call-to-action to browse or register.

![Landing Page](docs/screenshots/01-landing.png)

### Business Directory
Browse all registered businesses and book directly.

![Directory](docs/screenshots/08-directory.png)

### Booking Wizard
Four-step booking flow: choose a service, pick a date, select a time, confirm details.

![Booking Wizard](docs/screenshots/02-booking-wizard.png)

### Owner Dashboard
Business owners see their businesses with stats and all upcoming appointments across their businesses.

![Owner Dashboard](docs/screenshots/05-dashboard-owner.png)

### Customer Dashboard
Customers see only their own booked appointments with a link to browse businesses.

![Customer Dashboard](docs/screenshots/03-dashboard-customer.png)

### Business Management
Owner management hub with stats, quick actions (services, staff, contacts, vacancies, agenda, calendar), and recent appointments with action buttons.

![Business Management](docs/screenshots/06-business-management.png)

### Agenda View
Daily appointment view with status badges and action buttons (Confirm, Cancel, Serve) that follow the appointment state machine.

![Agenda](docs/screenshots/07-agenda.png)

### Authentication
Login page with email/password. Registration available for new users.

![Login](docs/screenshots/04-login.png)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | React 19 + TypeScript |
| Routing | Inertia.js (SPA feel, no separate API) |
| Styling | Tailwind CSS |
| Build | Vite |
| Database | MySQL 8 (SQLite for dev) |
| Auth | Laravel Breeze |

## Architecture

```
modern/
├── app/
│   ├── Enums/              # UserRole, AppointmentStatus, BookingStrategy
│   ├── Http/Controllers/   # Inertia controllers + API v1
│   ├── Models/             # Eloquent models (Business, Service, Staff, etc.)
│   ├── Policies/           # Role-based authorization
│   └── Services/           # AvailabilityService, BookingService, SlotGenerator
├── resources/js/
│   ├── Components/         # Reusable UI components
│   ├── Layouts/            # AuthenticatedLayout, GuestLayout
│   ├── Pages/              # Inertia pages (Dashboard, Booking, Business/*)
│   └── types/              # TypeScript interfaces
├── database/
│   ├── migrations/         # Schema definitions
│   └── seeders/            # DemoSeeder with sample data
└── routes/
    ├── web.php             # Inertia routes
    └── api.php             # JSON API for availability
```

## User Roles

| Role | Can Do |
|------|--------|
| **Root** | Everything (super admin) |
| **Owner** | Create/manage businesses, services, staff, vacancies, view agenda |
| **Customer** | Browse businesses, book appointments, view own appointments |

## Appointment State Machine

```
reserved ──→ confirmed ──→ served
    │              │
    └──→ canceled ←┘
```

- `reserved` → `confirmed` or `canceled`
- `confirmed` → `served` or `canceled`
- `canceled` and `served` are terminal states

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8 (or SQLite for local dev)

### Installation

```bash
cd modern

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed --class=DemoSeeder

# Build frontend
npm run build

# Start server
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000).

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Root | root@timegrid.io | password |
| Owner | owner@timegrid.io | password |
| Customer | customer@timegrid.io | password |

### Development

Run the Vite dev server for hot reload:

```bash
npm run dev
```

## Key Features

- **Public booking page** — customers browse services, pick date/time, and book (login required to submit)
- **Role-based dashboards** — owners manage businesses, customers view their appointments
- **Vacancy-driven availability** — business owners define time windows, the system generates bookable slots
- **Appointment lifecycle** — reserve, confirm, serve, or cancel with enforced state transitions
- **Multi-step booking wizard** — guided 4-step flow with real-time availability checks
- **Business management** — services, staff, contacts, vacancies, agenda, and calendar views

## License

[MIT](https://opensource.org/licenses/MIT)
