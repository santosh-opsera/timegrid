<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Timegridio\Concierge\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'medical-clinic',
                'name' => 'Medical Clinic',
                'description' => 'Primary care, specialist consultations, and preventive health services',
                'strategy' => 'timeslot',
            ],
            [
                'slug' => 'hair-salon',
                'name' => 'Hair Salon',
                'description' => 'Haircuts, styling, coloring, and grooming services',
                'strategy' => 'timeslot',
            ],
            [
                'slug' => 'yoga-studio',
                'name' => 'Yoga Studio',
                'description' => 'Group classes, private sessions, and wellness workshops',
                'strategy' => 'timeslot',
            ],
            [
                'slug' => 'auto-repair',
                'name' => 'Auto Repair',
                'description' => 'Vehicle maintenance, diagnostics, and repair services',
                'strategy' => 'timeslot',
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
