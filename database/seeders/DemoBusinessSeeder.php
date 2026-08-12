<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Timegridio\Concierge\Enums\AppointmentStatus;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;
use Timegridio\Concierge\Models\Contact;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Models\Vacancy;

class DemoBusinessSeeder extends Seeder
{
    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, Business> */
    private array $businesses = [];

    /** @var array<string, list<Service>> */
    private array $services = [];

    /** @var array<string, list<object{id: int, name: string}>> */
    private array $staff = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedBusinesses();
        $this->seedBusinessPreferences();
        $this->seedStaff();
        $this->seedServices();
        $this->seedContacts();
        $this->seedVacancies();
        $this->seedAppointments();
    }

    private function seedUsers(): void
    {
        $definitions = [
            'admin' => [
                'name' => 'System Administrator',
                'email' => 'admin@timegrid.io',
                'username' => 'admin',
                'password' => 'password',
                'role' => 'root',
            ],
            'sarah' => [
                'name' => 'Sarah Mitchell',
                'email' => 'sarah.clinic@example.com',
                'username' => 'sarah.mitchell',
                'password' => 'password',
                'role' => 'manager',
            ],
            'mike' => [
                'name' => 'Mike Rodriguez',
                'email' => 'mike.salon@example.com',
                'username' => 'mike.rodriguez',
                'password' => 'password',
                'role' => 'manager',
            ],
            'yoga' => [
                'name' => 'Maya Patel',
                'email' => 'yoga.instructor@example.com',
                'username' => 'maya.patel',
                'password' => 'password',
                'role' => 'manager',
            ],
            'mechanic' => [
                'name' => 'Jake Morrison',
                'email' => 'mechanic@example.com',
                'username' => 'jake.morrison',
                'password' => 'password',
                'role' => 'manager',
            ],
            'john' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'username' => 'john.doe',
                'password' => 'password',
                'role' => 'user',
            ],
            'jane' => [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'username' => 'jane.smith',
                'password' => 'password',
                'role' => 'user',
            ],
        ];

        foreach ($definitions as $key => $definition) {
            $user = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'username' => $definition['username'],
                    'password' => Hash::make($definition['password']),
                    'email_verified_at' => now(),
                ],
            );

            $user->assignRole($definition['role']);

            $this->users[$key] = $user;
        }
    }

    private function seedBusinesses(): void
    {
        $categories = Category::query()->pluck('id', 'slug');

        $definitions = [
            'medical' => [
                'owner' => 'sarah',
                'category_slug' => 'medical-clinic',
                'name' => 'Downtown Medical Center',
                'description' => 'Full-service primary care clinic offering same-day appointments, preventive screenings, and coordinated specialist referrals in the heart of Manhattan.',
                'timezone' => 'America/New_York',
                'locale' => 'en_US',
                'country_code' => 'US',
                'phone' => '+1 (212) 555-0142',
                'postal_address' => '425 Lexington Avenue, New York, NY 10017',
                'social_facebook' => 'https://www.facebook.com/downtownmedicalcenter',
                'start_at' => '08:00:00',
                'finish_at' => '18:00:00',
            ],
            'salon' => [
                'owner' => 'mike',
                'category_slug' => 'hair-salon',
                'name' => 'Elite Cuts Barbershop',
                'description' => 'Modern barbershop specializing in precision cuts, classic shaves, and premium grooming for professionals on the go.',
                'timezone' => 'America/Chicago',
                'locale' => 'en_US',
                'country_code' => 'US',
                'phone' => '+1 (312) 555-0198',
                'postal_address' => '742 North Wells Street, Chicago, IL 60654',
                'social_facebook' => 'https://www.facebook.com/elitecutsbarbershop',
                'start_at' => '09:00:00',
                'finish_at' => '20:00:00',
            ],
            'yoga' => [
                'owner' => 'yoga',
                'category_slug' => 'yoga-studio',
                'name' => 'Zen Flow Yoga',
                'description' => 'Boutique yoga studio with daily vinyasa, restorative, and prenatal classes plus private instruction in a calm, light-filled space.',
                'timezone' => 'America/Los_Angeles',
                'locale' => 'en_US',
                'country_code' => 'US',
                'phone' => '+1 (310) 555-0176',
                'postal_address' => '1847 Ocean Avenue, Santa Monica, CA 90401',
                'social_facebook' => 'https://www.facebook.com/zenflowyoga',
                'start_at' => '06:30:00',
                'finish_at' => '21:00:00',
            ],
            'auto' => [
                'owner' => 'mechanic',
                'category_slug' => 'auto-repair',
                'name' => 'AutoCare Express',
                'description' => 'Trusted neighborhood auto shop providing fast oil changes, brake service, diagnostics, and honest estimates without the dealership markup.',
                'timezone' => 'America/Denver',
                'locale' => 'en_US',
                'country_code' => 'US',
                'phone' => '+1 (303) 555-0133',
                'postal_address' => '2890 Blake Street, Denver, CO 80205',
                'social_facebook' => 'https://www.facebook.com/autocareexpress',
                'start_at' => '07:30:00',
                'finish_at' => '18:30:00',
            ],
        ];

        foreach ($definitions as $key => $definition) {
            $business = Business::query()->updateOrCreate(
                ['slug' => str($definition['name'])->slug()->toString()],
                [
                    'category_id' => $categories[$definition['category_slug']],
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'timezone' => $definition['timezone'],
                    'locale' => $definition['locale'],
                    'country_code' => $definition['country_code'],
                    'phone' => $definition['phone'],
                    'postal_address' => $definition['postal_address'],
                    'social_facebook' => $definition['social_facebook'],
                    'strategy' => 'timeslot',
                    'plan' => 'free',
                    'listed' => true,
                ],
            );

            $business->owners()->syncWithoutDetaching([$this->users[$definition['owner']]->id]);

            $business->pref('start_at', $definition['start_at'], 'time');
            $business->pref('finish_at', $definition['finish_at'], 'time');
            $business->pref('availability_future_days', 14, 'int');
            $business->pref('service_default_duration', 30, 'int');
            $business->pref('appointment_cancellation_pre_hs', 24, 'int');
            $business->pref('time_format', 'g:i A', 'string');
            $business->pref('date_format', 'M j, Y', 'string');

            $this->businesses[$key] = $business;
        }
    }

    private function seedBusinessPreferences(): void
    {
        // Preferences are applied during business creation; this hook remains for future overrides.
    }

    private function seedStaff(): void
    {
        $definitions = [
            'medical' => [
                ['name' => 'Dr. Sarah Mitchell'],
                ['name' => 'Dr. James Chen'],
                ['name' => 'Patricia Walsh, RN'],
            ],
            'salon' => [
                ['name' => 'Mike Rodriguez'],
                ['name' => 'Carlos Vega'],
                ['name' => 'Tyler Brooks'],
            ],
            'yoga' => [
                ['name' => 'Maya Patel'],
                ['name' => "Liam O'Brien"],
                ['name' => 'Sofia Reyes'],
            ],
            'auto' => [
                ['name' => 'Jake Morrison'],
                ['name' => 'Tony Nguyen'],
                ['name' => 'Rachel Kim'],
            ],
        ];

        foreach ($definitions as $businessKey => $members) {
            $this->staff[$businessKey] = [];

            foreach ($members as $member) {
                $staffId = DB::table('humanresources')->insertGetId([
                    'business_id' => $this->businesses[$businessKey]->id,
                    'name' => $member['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->staff[$businessKey][] = (object) [
                    'id' => $staffId,
                    'name' => $member['name'],
                ];
            }
        }
    }

    private function seedServices(): void
    {
        $definitions = [
            'medical' => [
                ['name' => 'General Consultation', 'duration' => 30, 'color' => '#4A90D9', 'description' => 'Routine visit for symptoms, prescriptions, and care planning.'],
                ['name' => 'Annual Physical Exam', 'duration' => 45, 'color' => '#2ECC71', 'description' => 'Comprehensive wellness exam with vitals, labs review, and health goals.'],
                ['name' => 'Vaccination', 'duration' => 15, 'color' => '#E74C3C', 'description' => 'Flu, COVID, travel, and routine immunizations administered on-site.'],
                ['name' => 'Blood Work Panel', 'duration' => 20, 'color' => '#9B59B6', 'description' => 'On-site phlebotomy for standard and custom lab orders.'],
                ['name' => 'Specialist Referral Consult', 'duration' => 30, 'color' => '#F39C12', 'description' => 'Evaluation and referral coordination for cardiology, orthopedics, and more.'],
            ],
            'salon' => [
                ['name' => 'Classic Haircut', 'duration' => 30, 'color' => '#34495E', 'description' => 'Shampoo, cut, and style tailored to your look.'],
                ['name' => 'Beard Trim & Shape', 'duration' => 20, 'color' => '#8E44AD', 'description' => 'Precision beard line-up with hot towel finish.'],
                ['name' => 'Hot Towel Shave', 'duration' => 45, 'color' => '#16A085', 'description' => 'Traditional straight-razor shave with aromatherapy oils.'],
                ['name' => 'Hair Color & Highlights', 'duration' => 90, 'color' => '#D35400', 'description' => 'Single-process color or partial highlights with consultation.'],
                ['name' => 'Kids Cut (12 & under)', 'duration' => 25, 'color' => '#27AE60', 'description' => 'Patient, kid-friendly cut in a relaxed environment.'],
            ],
            'yoga' => [
                ['name' => 'Vinyasa Flow (All Levels)', 'duration' => 60, 'color' => '#1ABC9C', 'description' => 'Breath-linked movement building strength and flexibility.'],
                ['name' => 'Restorative Yoga', 'duration' => 75, 'color' => '#3498DB', 'description' => 'Supported poses and guided relaxation for deep recovery.'],
                ['name' => 'Private Yoga Session', 'duration' => 60, 'color' => '#E67E22', 'description' => 'One-on-one instruction customized to your body and goals.'],
                ['name' => 'Meditation Workshop', 'duration' => 45, 'color' => '#8E44AD', 'description' => 'Guided mindfulness techniques for stress reduction.'],
                ['name' => 'Prenatal Yoga', 'duration' => 60, 'color' => '#E91E63', 'description' => 'Safe, nurturing practice designed for expecting mothers.'],
            ],
            'auto' => [
                ['name' => 'Express Oil Change', 'duration' => 30, 'color' => '#F1C40F', 'description' => 'Synthetic blend oil change with multi-point inspection.'],
                ['name' => 'Brake Inspection', 'duration' => 45, 'color' => '#C0392B', 'description' => 'Pad, rotor, and fluid check with written report.'],
                ['name' => 'Tire Rotation & Balance', 'duration' => 40, 'color' => '#2C3E50', 'description' => 'Extend tire life with rotation, balance, and pressure check.'],
                ['name' => 'Engine Diagnostic', 'duration' => 60, 'color' => '#7F8C8D', 'description' => 'Computer scan and technician review for check-engine issues.'],
                ['name' => 'A/C Performance Check', 'duration' => 35, 'color' => '#2980B9', 'description' => 'Refrigerant level, leak detection, and vent temperature test.'],
            ],
        ];

        foreach ($definitions as $businessKey => $services) {
            $this->services[$businessKey] = [];

            foreach ($services as $service) {
                $this->services[$businessKey][] = Service::query()->create([
                    'business_id' => $this->businesses[$businessKey]->id,
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'duration' => $service['duration'],
                    'color' => $service['color'],
                ]);
            }
        }
    }

    private function seedContacts(): void
    {
        $contactsByBusiness = [
            'medical' => [
                ['firstname' => 'Emily', 'lastname' => 'Hartman', 'email' => 'emily.hartman@gmail.com', 'mobile' => '9175550101', 'gender' => 'F', 'occupation' => 'Marketing Director'],
                ['firstname' => 'Robert', 'lastname' => 'Nguyen', 'email' => 'robert.nguyen@outlook.com', 'mobile' => '6465550102', 'gender' => 'M', 'occupation' => 'Software Engineer'],
                ['firstname' => 'Lisa', 'lastname' => 'Goldstein', 'email' => 'lisa.goldstein@yahoo.com', 'mobile' => '2125550103', 'gender' => 'F', 'occupation' => 'Attorney'],
                ['firstname' => 'David', 'lastname' => 'Park', 'email' => 'david.park@icloud.com', 'mobile' => '7185550104', 'gender' => 'M', 'occupation' => 'Teacher'],
                ['firstname' => 'Maria', 'lastname' => 'Santos', 'email' => 'maria.santos@gmail.com', 'mobile' => '3475550105', 'gender' => 'F', 'occupation' => 'Nurse'],
                ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john.doe@example.com', 'mobile' => '2125550199', 'gender' => 'M', 'occupation' => 'Account Manager', 'user_key' => 'john'],
            ],
            'salon' => [
                ['firstname' => 'Andrew', 'lastname' => 'Collins', 'email' => 'andrew.collins@gmail.com', 'mobile' => '3125550201', 'gender' => 'M', 'occupation' => 'Financial Analyst'],
                ['firstname' => 'Nicole', 'lastname' => 'Baker', 'email' => 'nicole.baker@outlook.com', 'mobile' => '7735550202', 'gender' => 'F', 'occupation' => 'Graphic Designer'],
                ['firstname' => 'Marcus', 'lastname' => 'Thompson', 'email' => 'marcus.thompson@gmail.com', 'mobile' => '3125550203', 'gender' => 'M', 'occupation' => 'Real Estate Agent'],
                ['firstname' => 'Sophia', 'lastname' => 'Lee', 'email' => 'sophia.lee@yahoo.com', 'mobile' => '8475550204', 'gender' => 'F', 'occupation' => 'Consultant'],
                ['firstname' => 'Jane', 'lastname' => 'Smith', 'email' => 'jane.smith@example.com', 'mobile' => '3125550299', 'gender' => 'F', 'occupation' => 'Product Manager', 'user_key' => 'jane'],
            ],
            'yoga' => [
                ['firstname' => 'Olivia', 'lastname' => 'Ramirez', 'email' => 'olivia.ramirez@gmail.com', 'mobile' => '3105550301', 'gender' => 'F', 'occupation' => 'Photographer'],
                ['firstname' => 'Ethan', 'lastname' => 'Brooks', 'email' => 'ethan.brooks@outlook.com', 'mobile' => '4245550302', 'gender' => 'M', 'occupation' => 'Startup Founder'],
                ['firstname' => 'Hannah', 'lastname' => 'Kim', 'email' => 'hannah.kim@gmail.com', 'mobile' => '3105550303', 'gender' => 'F', 'occupation' => 'Physical Therapist'],
                ['firstname' => 'Daniel', 'lastname' => 'Foster', 'email' => 'daniel.foster@icloud.com', 'mobile' => '8185550304', 'gender' => 'M', 'occupation' => 'Architect'],
                ['firstname' => 'Ava', 'lastname' => 'Martinez', 'email' => 'ava.martinez@yahoo.com', 'mobile' => '3105550305', 'gender' => 'F', 'occupation' => 'HR Manager'],
            ],
            'auto' => [
                ['firstname' => 'Chris', 'lastname' => 'Anderson', 'email' => 'chris.anderson@gmail.com', 'mobile' => '3035550401', 'gender' => 'M', 'occupation' => 'Sales Manager'],
                ['firstname' => 'Jennifer', 'lastname' => 'Walsh', 'email' => 'jennifer.walsh@outlook.com', 'mobile' => '7205550402', 'gender' => 'F', 'occupation' => 'Dental Hygienist'],
                ['firstname' => 'Kevin', 'lastname' => 'OConnor', 'email' => 'kevin.oconnor@gmail.com', 'mobile' => '3035550403', 'gender' => 'M', 'occupation' => 'Electrician'],
                ['firstname' => 'Rachel', 'lastname' => 'Davis', 'email' => 'rachel.davis@yahoo.com', 'mobile' => '3035550404', 'gender' => 'F', 'occupation' => 'Event Planner'],
                ['firstname' => 'Brian', 'lastname' => 'Stewart', 'email' => 'brian.stewart@icloud.com', 'mobile' => '7205550405', 'gender' => 'M', 'occupation' => 'Chef'],
                ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john.doe@example.com', 'mobile' => '3035550499', 'gender' => 'M', 'occupation' => 'Account Manager', 'user_key' => 'john'],
            ],
        ];

        foreach ($contactsByBusiness as $businessKey => $contacts) {
            $business = $this->businesses[$businessKey];

            foreach ($contacts as $contactData) {
                $userId = isset($contactData['user_key'])
                    ? $this->users[$contactData['user_key']]->id
                    : null;

                $contact = Contact::query()->create([
                    'firstname' => $contactData['firstname'],
                    'lastname' => $contactData['lastname'],
                    'email' => $contactData['email'],
                    'mobile' => $contactData['mobile'],
                    'gender' => $contactData['gender'],
                    'occupation' => $contactData['occupation'],
                    'birthdate' => Carbon::now()->subYears(rand(25, 55))->subDays(rand(1, 365)),
                    'postal_address' => $contactData['firstname'].' '.$contactData['lastname'].' Ave, Demo City',
                    'user_id' => $userId,
                ]);

                $business->contacts()->attach($contact->id);
            }
        }
    }

    private function seedVacancies(): void
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(13);

        foreach ($this->businesses as $businessKey => $business) {
            $open = $business->pref('start_at');
            $close = $business->pref('finish_at');

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekend() && in_array($businessKey, ['medical', 'auto'], true)) {
                    continue;
                }

                foreach ($this->services[$businessKey] as $index => $service) {
                    $staffMember = $this->staff[$businessKey][$index % count($this->staff[$businessKey])];
                    $dayStart = $date->copy()->setTimeFromTimeString((string) $open);
                    $dayEnd = $date->copy()->setTimeFromTimeString((string) $close);

                    Vacancy::query()->create([
                        'business_id' => $business->id,
                        'service_id' => $service->id,
                        'humanresource_id' => $staffMember->id,
                        'date' => $date->toDateString(),
                        'start_at' => $dayStart,
                        'finish_at' => $dayEnd,
                        'capacity' => $businessKey === 'yoga' ? 12 : 1,
                    ]);
                }
            }
        }
    }

    private function seedAppointments(): void
    {
        $scenarios = [
            [
                'business_key' => 'medical',
                'contact_email' => 'emily.hartman@gmail.com',
                'service_index' => 0,
                'staff_index' => 0,
                'days_offset' => -5,
                'hour' => 10,
                'status' => AppointmentStatus::Served,
                'comments' => 'Follow-up on seasonal allergies; patient reported improvement.',
            ],
            [
                'business_key' => 'medical',
                'contact_email' => 'robert.nguyen@outlook.com',
                'service_index' => 1,
                'staff_index' => 1,
                'days_offset' => -2,
                'hour' => 14,
                'status' => AppointmentStatus::Confirmed,
                'comments' => 'Annual physical — fasting labs completed beforehand.',
            ],
            [
                'business_key' => 'medical',
                'contact_email' => 'john.doe@example.com',
                'service_index' => 2,
                'staff_index' => 2,
                'days_offset' => 3,
                'hour' => 11,
                'status' => AppointmentStatus::Reserved,
                'comments' => 'Flu vaccination appointment.',
            ],
            [
                'business_key' => 'salon',
                'contact_email' => 'andrew.collins@gmail.com',
                'service_index' => 0,
                'staff_index' => 0,
                'days_offset' => -7,
                'hour' => 17,
                'status' => AppointmentStatus::Served,
                'comments' => 'Regular monthly trim.',
            ],
            [
                'business_key' => 'salon',
                'contact_email' => 'jane.smith@example.com',
                'service_index' => 1,
                'staff_index' => 1,
                'days_offset' => 1,
                'hour' => 12,
                'status' => AppointmentStatus::Reserved,
                'comments' => 'Beard trim before client presentation.',
            ],
            [
                'business_key' => 'salon',
                'contact_email' => 'marcus.thompson@gmail.com',
                'service_index' => 2,
                'staff_index' => 0,
                'days_offset' => 5,
                'hour' => 16,
                'status' => AppointmentStatus::Confirmed,
                'comments' => 'Hot towel shave for wedding weekend.',
            ],
            [
                'business_key' => 'yoga',
                'contact_email' => 'olivia.ramirez@gmail.com',
                'service_index' => 0,
                'staff_index' => 0,
                'days_offset' => -3,
                'hour' => 7,
                'status' => AppointmentStatus::Served,
                'comments' => 'Morning vinyasa class check-in.',
            ],
            [
                'business_key' => 'yoga',
                'contact_email' => 'hannah.kim@gmail.com',
                'service_index' => 2,
                'staff_index' => 0,
                'days_offset' => 2,
                'hour' => 9,
                'status' => AppointmentStatus::Confirmed,
                'comments' => 'Private session focusing on hip mobility.',
            ],
            [
                'business_key' => 'yoga',
                'contact_email' => 'daniel.foster@icloud.com',
                'service_index' => 1,
                'staff_index' => 1,
                'days_offset' => 6,
                'hour' => 18,
                'status' => AppointmentStatus::Reserved,
                'comments' => 'First restorative yoga visit.',
            ],
            [
                'business_key' => 'auto',
                'contact_email' => 'chris.anderson@gmail.com',
                'service_index' => 0,
                'staff_index' => 0,
                'days_offset' => -4,
                'hour' => 8,
                'status' => AppointmentStatus::Served,
                'comments' => 'Synthetic oil change completed; next service in 5,000 miles.',
            ],
            [
                'business_key' => 'auto',
                'contact_email' => 'kevin.oconnor@gmail.com',
                'service_index' => 1,
                'staff_index' => 1,
                'days_offset' => -1,
                'hour' => 13,
                'status' => AppointmentStatus::Confirmed,
                'comments' => 'Brake squeal on front left — inspection scheduled.',
            ],
            [
                'business_key' => 'auto',
                'contact_email' => 'john.doe@example.com',
                'service_index' => 3,
                'staff_index' => 0,
                'days_offset' => 4,
                'hour' => 10,
                'status' => AppointmentStatus::Reserved,
                'comments' => 'Check engine light appeared yesterday evening.',
            ],
            [
                'business_key' => 'medical',
                'contact_email' => 'lisa.goldstein@yahoo.com',
                'service_index' => 3,
                'staff_index' => 2,
                'days_offset' => 7,
                'hour' => 8,
                'status' => AppointmentStatus::Reserved,
                'comments' => 'Fasting blood panel before 9 AM.',
            ],
            [
                'business_key' => 'salon',
                'contact_email' => 'nicole.baker@outlook.com',
                'service_index' => 3,
                'staff_index' => 2,
                'days_offset' => 8,
                'hour' => 14,
                'status' => AppointmentStatus::Confirmed,
                'comments' => 'Partial highlights — reference photo sent via email.',
            ],
            [
                'business_key' => 'yoga',
                'contact_email' => 'ava.martinez@yahoo.com',
                'service_index' => 4,
                'staff_index' => 2,
                'days_offset' => 9,
                'hour' => 10,
                'status' => AppointmentStatus::Reserved,
                'comments' => 'Second trimester prenatal class.',
            ],
            [
                'business_key' => 'auto',
                'contact_email' => 'rachel.davis@yahoo.com',
                'service_index' => 2,
                'staff_index' => 2,
                'days_offset' => 10,
                'hour' => 15,
                'status' => AppointmentStatus::Confirmed,
                'comments' => 'Tire rotation before road trip.',
            ],
        ];

        foreach ($scenarios as $scenario) {
            $business = $this->businesses[$scenario['business_key']];
            $service = $this->services[$scenario['business_key']][$scenario['service_index']];
            $staffMember = $this->staff[$scenario['business_key']][$scenario['staff_index']];

            $contact = Contact::query()
                ->where('email', $scenario['contact_email'])
                ->whereHas('businesses', fn ($query) => $query->where('businesses.id', $business->id))
                ->firstOrFail();

            $startAt = Carbon::today()
                ->addDays($scenario['days_offset'])
                ->setTime($scenario['hour'], 0);

            $duration = (int) $service->duration;
            $finishAt = $startAt->copy()->addMinutes($duration);

            $vacancy = Vacancy::query()
                ->where('business_id', $business->id)
                ->where('service_id', $service->id)
                ->whereDate('date', $startAt->toDateString())
                ->first();

            $issuerId = $contact->user_id ?? $business->owner()?->id;

            Appointment::query()->create([
                'issuer_id' => $issuerId,
                'contact_id' => $contact->id,
                'business_id' => $business->id,
                'service_id' => $service->id,
                'humanresource_id' => $staffMember->id,
                'vacancy_id' => $vacancy?->id,
                'start_at' => $startAt,
                'finish_at' => $finishAt,
                'duration' => $duration,
                'status' => $scenario['status'],
                'comments' => $scenario['comments'],
            ]);
        }
    }
}
