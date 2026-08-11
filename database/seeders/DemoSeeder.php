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
        $root = User::create([
            'name' => 'Root Admin',
            'email' => 'root@timegrid.io',
            'password' => 'password',
            'role' => UserRole::Root,
        ]);

        $owner = User::create([
            'name' => 'Spa Owner',
            'email' => 'owner@timegrid.io',
            'password' => 'password',
            'role' => UserRole::Owner,
        ]);

        $customer = User::create([
            'name' => 'Jane Customer',
            'email' => 'customer@timegrid.io',
            'password' => 'password',
            'role' => UserRole::Customer,
        ]);

        $business = Business::create([
            'name' => 'Bella Spa',
            'slug' => 'bella-spa',
            'description' => 'A relaxing spa experience in the heart of the city.',
            'category' => 'Beauty & Wellness',
            'timezone' => 'America/New_York',
            'strategy' => BookingStrategy::Timeslot,
        ]);

        $business->owners()->attach($owner->id, ['role' => 'owner']);

        $services = collect([
            ['name' => 'Haircut', 'duration' => 30, 'color' => '#3B82F6'],
            ['name' => 'Massage', 'duration' => 60, 'color' => '#10B981'],
            ['name' => 'Facial', 'duration' => 45, 'color' => '#F59E0B'],
        ])->map(fn ($data) => Service::create([
            'business_id' => $business->id,
            ...$data,
        ]));

        $staff = collect(['Alice', 'Bob'])->map(fn ($name) => Staff::create([
            'business_id' => $business->id,
            'name' => $name,
        ]));

        $contacts = collect([
            ['firstname' => 'Jane', 'lastname' => 'Customer', 'email' => 'customer@timegrid.io', 'user_id' => $customer->id],
            ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com'],
            ['firstname' => 'Sarah', 'lastname' => 'Smith', 'email' => 'sarah@example.com'],
            ['firstname' => 'Mike', 'lastname' => 'Johnson', 'email' => 'mike@example.com'],
        ])->map(fn ($data) => Contact::create([
            'business_id' => $business->id,
            ...$data,
        ]));

        $today = Carbon::today($business->timezone);

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

        $haircut = $services->firstWhere('name', 'Haircut');
        $massage = $services->firstWhere('name', 'Massage');
        $facial = $services->firstWhere('name', 'Facial');

        $appointmentData = [
            [
                'service' => $haircut,
                'contact' => $contacts[0],
                'staff' => $staff[0],
                'status' => AppointmentStatus::Reserved,
                'days' => 1,
                'time' => '09:00',
            ],
            [
                'service' => $massage,
                'contact' => $contacts[1],
                'staff' => $staff[1],
                'status' => AppointmentStatus::Confirmed,
                'days' => 2,
                'time' => '10:00',
            ],
            [
                'service' => $facial,
                'contact' => $contacts[2],
                'staff' => $staff[0],
                'status' => AppointmentStatus::Canceled,
                'days' => 3,
                'time' => '11:00',
            ],
            [
                'service' => $haircut,
                'contact' => $contacts[3],
                'staff' => $staff[1],
                'status' => AppointmentStatus::Served,
                'days' => -2,
                'time' => '14:00',
            ],
            [
                'service' => $massage,
                'contact' => $contacts[0],
                'staff' => $staff[0],
                'status' => AppointmentStatus::Confirmed,
                'days' => 4,
                'time' => '15:00',
            ],
        ];

        foreach ($appointmentData as $data) {
            $startAt = $today->copy()->addDays($data['days'])->setTimeFromTimeString($data['time']);

            Appointment::create([
                'business_id' => $business->id,
                'service_id' => $data['service']->id,
                'contact_id' => $data['contact']->id,
                'staff_id' => $data['staff']->id,
                'status' => $data['status'],
                'start_at' => $startAt,
                'end_at' => $startAt->copy()->addMinutes($data['service']->duration),
                'duration' => $data['service']->duration,
                'hash' => Str::random(32),
            ]);
        }
    }
}
