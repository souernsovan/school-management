<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Admin role exists
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'               => 'Administrator',
                'password'           => Hash::make('password'),
                'email_verified_at'  => now(),
            ]
        );
        $admin->syncRoles(['Admin']);

        // Create default teacher user
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'name'               => 'Teacher',
                'password'           => Hash::make('password'),
                'email_verified_at'  => now(),
            ]
        );
        $teacher->syncRoles(['Teacher']);
    }
}
