<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        // ✅ Permissions
        $permissions = [
            'manage users',
            'manage students',
            'manage classes',
            'manage teachers',
            'manage attendance',
            'manage subjects',
            'manage timetables',
            'manage exams',
            'view students',
            'view classes',
            'view subjects',
            'view teachers',
            'view timetable',
            'view results',
            'create students',
            'create classes',
            'create subjects',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 👑 Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $student = Role::firstOrCreate(['name' => 'Student']);

        // 🔐 Assign permissions

        // Admin = everything
        $admin->givePermissionTo(Permission::all());

        // Teacher permissions
        $teacher->givePermissionTo([
            'view students',
            'view classes',
            'view subjects',
            'view teachers',
            'view timetable',
            'view results',
            'manage attendance',
            'manage exams',
            'manage timetables',
            'create students',
            'create classes',
            'create subjects',
        ]);

        // Student permissions
        $student->givePermissionTo([
            'view results',
            'view timetable',
        ]);
    }
}
