# Timegrid

**Online appointment scheduling for businesses and customers.**

Timegrid connects service providers with customers through frictionless, self-service booking. Manage staff, services, availability, and appointments from a single dashboard — while customers browse a directory and book in seconds.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 12 |
| Frontend | React 18, TypeScript 5, Inertia.js |
| Styling | Tailwind CSS 4 |
| Build | Vite 6 |
| Database | SQLite (dev) / MySQL 8+ / PostgreSQL 15+ |
| Auth | Laravel Sanctum, Socialite (OAuth) |
| Scheduling | [Concierge](packages/timegridio/concierge) (forked, modernized) |
| CI/CD | GitHub Actions |
| Containerization | Docker, Docker Compose |

## Legacy Refactoring Summary

This project was modernized from a **Laravel 5.3 / PHP 5.6** application (archived in 2019) to a production-grade **Laravel 12 / PHP 8.3+** application. The refactoring covered:

### Backend
- Upgraded from Laravel 5.3 to **Laravel 12** with all breaking changes resolved
- Migrated from PHP 5.6 to **PHP 8.3+** with strict types, typed properties, backed enums, and readonly DTOs
- Forked and modernized the unmaintained `timegridio/concierge` package in-repo
- Replaced 12+ abandoned packages (Beautymail, Notifynder, Jenssegers Agent, etc.)
- Rewrote all database migrations with proper foreign keys, soft deletes, and composite indexes
- Added mass assignment protection (`$fillable`) to every Eloquent model
- Implemented PII masking in logs, audit logging, and impersonation tracking
- Fixed critical security bugs (missing auth redirect, null-user middleware crash)
- Added FormRequest validation classes for all major operations
- Modernized all 30+ controllers with `declare(strict_types=1)` and typed signatures

### Frontend
- Replaced Blade/jQuery/AdminLTE with **React 18 + Inertia.js + Tailwind CSS 4**
- Built a complete single-page application with dark mode, responsive layout, and polished UI
- Implemented 20+ React page components covering all user and manager workflows
- Added client-side search, filtering, and category selection on the directory
- Built a weekly calendar view with appointment event rendering
- Created reusable UI component library (StatCard, Card, EmptyState, PageHeader, StatusBadge)

### Infrastructure
- Added GitHub Actions CI with PHPUnit, Pint, Composer audit, and SAST checks
- Created Docker and Docker Compose configs for development and production
- Added deployment, backup, rollback, and health-check scripts
- Created incident response runbook

---

## Features

- **Business Directory** — browse and search local service providers by category
- **Self-Service Booking** — customers select a date, time, and service to book instantly
- **Manager Dashboard** — real-time stats, today's agenda, notifications, and quick links
- **Services Management** — create, edit, and delete services with durations and descriptions
- **Staff Management** — assign staff members to services and time slots
- **Contacts / Addressbook** — manage customer relationships with notes
- **Availability / Vacancies** — configure weekly business hours and capacity per service
- **Calendar View** — weekly grid with color-coded appointment events and iCal export
- **Appointment Lifecycle** — reserved → confirmed → served / annulled status flow
- **Multi-Business Support** — one owner can manage multiple businesses
- **i18n** — English, Spanish, French, Italian, Russian, Armenian
- **Multi-Timezone** — per-business timezone configuration
- **Dark Mode** — system-aware with manual toggle
- **OAuth** — social login via Laravel Socialite

---

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer 2+
- Node.js 20+
- npm 10+

### Installation

```bash
git clone https://github.com/timegridio/timegrid.git
cd timegrid

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
npm run build

php artisan serve
```

The application will be available at `http://localhost:8000`.

### Docker

```bash
docker compose up -d
```

This starts the app with MySQL, Redis, and Mailpit.

---

## Demo Accounts

After running `php artisan migrate --seed`, the following accounts are available:

### Admin

| Name | Email | Password | Role |
|---|---|---|---|
| Admin | `admin@timegrid.io` | `password` | Root admin |

### Business Owners (Manager role)

| Name | Email | Password | Business |
|---|---|---|---|
| Sarah Mitchell | `sarah.clinic@example.com` | `password` | Downtown Medical Center |
| Mike Rodriguez | `mike.salon@example.com` | `password` | Elite Cuts Barbershop |
| Maya Patel | `yoga.instructor@example.com` | `password` | Zen Flow Yoga |
| Jake Morrison | `mechanic@example.com` | `password` | AutoCare Express |

### Customers (User role)

| Name | Email | Password | Notes |
|---|---|---|---|
| John Doe | `john.doe@example.com` | `password` | Has 2 upcoming appointments |
| Jane Smith | `jane.smith@example.com` | `password` | Has 1 upcoming appointment |

### Seeded Data

Each business comes with:
- 5 services with realistic names, descriptions, and durations
- 3 staff members
- 5–6 customer contacts
- 2 weeks of availability (weekdays, business hours)
- Pre-booked appointments in various statuses (reserved, confirmed, served)

---

## Project Structure

```
app/
├── Http/Controllers/       # Auth, Guest, User, Manager, Root controllers
├── Models/                 # User, Role, Permission, Preference, ActivityLog
├── Policies/               # BusinessPolicy authorization
├── Providers/              # App, Auth, Event, Route service providers
├── Notifications/          # BusinessActivityNotification
├── Logging/                # PiiMaskingProcessor
└── TG/                     # Business logic (Dashboard, TransMail, etc.)

packages/
└── timegridio/concierge/   # Forked scheduling library
    └── src/
        ├── Models/         # Business, Appointment, Service, Contact, etc.
        ├── Enums/          # AppointmentStatus backed enum
        ├── Calendar/       # Timeslot and dateslot calendars
        ├── Vacancy/        # Vacancy parsing and management
        └── Booking/        # Reservation logic

resources/js/
├── Components/             # UI.tsx, StatusBadge.tsx, LanguageSwitcher.tsx
├── Hooks/                  # useRoute, useTranslation
├── Layouts/                # AuthenticatedLayout, GuestLayout
├── Pages/
│   ├── Auth/               # Login, Register
│   ├── Dashboard.tsx       # Customer dashboard
│   ├── Directory.tsx       # Business directory with search/filter
│   ├── Appointments/       # Customer appointments list
│   ├── Booking/            # Business detail + booking flow
│   ├── Business/           # Manager pages (Show, Edit, Agenda, Staff, etc.)
│   ├── Manager/            # Dashboard, Calendar, Services, Contacts, etc.
│   └── Profile/            # Profile edit
└── types/                  # TypeScript type definitions

database/
├── migrations/             # 17 modern migrations with FKs and indexes
└── seeders/                # Categories, Roles, DemoBusinessSeeder
```

---

## Localization

Supported languages:

| Language | Code |
|---|---|
| American English | `en_US` |
| Spanish (Spain) | `es_ES` |
| Spanish (Argentina) | `es_AR` |
| Italian | `it_IT` |
| French | `fr_FR` |
| Russian | `ru_RU` |
| Armenian | `hy_AM` |

---

## Running Tests

```bash
php artisan test
```

## Code Quality

```bash
./vendor/bin/pint          # Code style (Laravel Pint)
composer audit             # Dependency vulnerability check
```

---

## License

Timegrid is open-sourced software licensed under the [AGPL-3.0](http://www.gnu.org/licenses/agpl-3.0-standalone.html).

---

## Original Credits

Timegrid was originally created by [Ariel Vallese](http://alariva.com). See the full list of [contributors](https://github.com/timegridio/timegrid/graphs/contributors).
