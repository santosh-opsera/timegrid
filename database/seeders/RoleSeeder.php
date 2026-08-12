<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'root',
                'name' => 'root',
                'description' => 'System administrator with full platform access',
            ],
            [
                'slug' => 'manager',
                'name' => 'manager',
                'description' => 'Business owner or manager with scheduling and addressbook access',
            ],
            [
                'slug' => 'user',
                'name' => 'user',
                'description' => 'Customer who books appointments with businesses',
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role,
            );
        }
    }
}
