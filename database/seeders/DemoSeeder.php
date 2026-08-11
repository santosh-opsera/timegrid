<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\BookingStrategy;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Contact;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'root@timegrid.io')->exists()) {
            $this->command?->info('Demo data already exists, skipping.');
            return;
        }

        $root = User::create([
            'name' => 'Root Admin',
            'email' => 'root@timegrid.io',
            'password' => 'password',
            'role' => UserRole::Root,
        ]);

        $owner = User::create([
            'name' => 'Business Owner',
            'email' => 'owner@timegrid.io',
            'password' => 'password',
            'role' => UserRole::Owner,
        ]);

        $customer = User::create([
            'name' => 'Happy Customer',
            'email' => 'customer@timegrid.io',
            'password' => 'password',
            'role' => UserRole::Customer,
        ]);

        // --- Business 1: Bella Spa & Wellness ---
        $spa = Business::create([
            'name' => 'Bella Spa & Wellness',
            'slug' => 'bella-spa-wellness',
            'description' => 'A relaxing spa experience in the heart of the city.',
            'category' => 'Beauty & Wellness',
            'phone' => '+1-555-100-2000',
            'timezone' => 'UTC',
            'strategy' => BookingStrategy::Timeslot,
        ]);
        $spa->owners()->attach($owner->id, ['role' => 'owner']);

        $spaServices = collect([
            ['name' => 'Swedish Massage', 'duration' => 60, 'color' => '#10B981'],
            ['name' => 'Deep Tissue Massage', 'duration' => 45, 'color' => '#3B82F6'],
            ['name' => 'Facial Treatment', 'duration' => 30, 'color' => '#F59E0B'],
        ])->map(fn ($data) => Service::create(['business_id' => $spa->id, ...$data]));

        $spaStaff = collect(['Sarah Johnson', 'Michael Chen'])
            ->map(fn ($name) => Staff::create(['business_id' => $spa->id, 'name' => $name]));

        // --- Business 2: Urban Cuts Barbershop ---
        $barber = Business::create([
            'name' => 'Urban Cuts Barbershop',
            'slug' => 'urban-cuts-barbershop',
            'description' => 'Modern barbershop offering haircuts, beard trims, and grooming services.',
            'category' => 'Barbershop',
            'phone' => '+1-555-200-3000',
            'timezone' => 'UTC',
            'strategy' => BookingStrategy::Timeslot,
        ]);
        $barber->owners()->attach($owner->id, ['role' => 'owner']);

        $barberServices = collect([
            ['name' => 'Haircut', 'duration' => 30, 'color' => '#6366F1'],
            ['name' => 'Beard Trim', 'duration' => 20, 'color' => '#EF4444'],
            ['name' => 'Full Grooming', 'duration' => 60, 'color' => '#8B5CF6'],
        ])->map(fn ($data) => Service::create(['business_id' => $barber->id, ...$data]));

        $barberStaff = collect(['James Rodriguez', 'Alex Kim'])
            ->map(fn ($name) => Staff::create(['business_id' => $barber->id, 'name' => $name]));

        // --- Vacancies (next 14 days, skip Sundays) ---
        $today = Carbon::today();

        $this->createVacancies($spa, $spaServices, $today);
        $this->createVacancies($barber, $barberServices, $today);

        // --- Contacts ---
        $spaContacts = collect([
            ['firstname' => 'Happy', 'lastname' => 'Customer', 'email' => 'customer@timegrid.io', 'user_id' => $customer->id],
            ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com'],
            ['firstname' => 'Sarah', 'lastname' => 'Smith', 'email' => 'sarah@example.com'],
            ['firstname' => 'Mike', 'lastname' => 'Johnson', 'email' => 'mike@example.com'],
        ])->map(fn ($data) => Contact::create(['business_id' => $spa->id, ...$data]));

        $barberContacts = collect([
            ['firstname' => 'Happy', 'lastname' => 'Customer', 'email' => 'customer@timegrid.io', 'user_id' => $customer->id],
            ['firstname' => 'Tom', 'lastname' => 'Williams', 'email' => 'tom@example.com'],
        ])->map(fn ($data) => Contact::create(['business_id' => $barber->id, ...$data]));

        // --- Sample Appointments ---
        $this->createSampleAppointments($spa, $spaServices, $spaContacts, $spaStaff, $today);
        $this->createSampleAppointments($barber, $barberServices, $barberContacts, $barberStaff, $today);
    }

    private function createVacancies(Business $business, $services, Carbon $today): void
    {
        for ($i = 0; $i < 14; $i++) {
            $date = $today->copy()->addDays($i);

            if ($date->isSunday()) {
                continue;
            }

            foreach ($services as $service) {
                Vacancy::create([
                    'business_id' => $business->id,
                    'service_id' => $service->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                ]);
            }
        }
    }

    private function createSampleAppointments(Business $business, $services, $contacts, $staff, Carbon $today): void
    {
        $statuses = [
            AppointmentStatus::Reserved,
            AppointmentStatus::Confirmed,
            AppointmentStatus::Served,
        ];

        $serviceList = $services->values();
        $staffList = $staff->values();
        $contactList = $contacts->values();

        foreach ([1, 2, 4] as $idx => $daysAhead) {
            $service = $serviceList[$idx % $serviceList->count()];
            $staffMember = $staffList[$idx % $staffList->count()];
            $contact = $contactList[$idx % $contactList->count()];
            $startAt = $today->copy()->addDays($daysAhead)->setTimeFromTimeString(sprintf('%02d:00', 9 + $idx));

            Appointment::create([
                'business_id' => $business->id,
                'service_id' => $service->id,
                'contact_id' => $contact->id,
                'staff_id' => $staffMember->id,
                'status' => $statuses[$idx],
                'start_at' => $startAt,
                'end_at' => $startAt->copy()->addMinutes($service->duration),
                'duration' => $service->duration,
                'hash' => Str::random(32),
            ]);
        }
    }
}
