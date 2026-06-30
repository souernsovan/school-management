<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $permissions = [
        'view students',
        'create students',
        'edit students',
        'delete students',

        'view teachers',
        'manage attendance',

        'view classes',
        'create classes',

        'manage fees',
        'view payments',

        'manage users',
        'manage roles',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Roles
    $admin = Role::firstOrCreate(['name' => 'Admin']);
    $teacher = Role::firstOrCreate(['name' => 'Teacher']);
    $student = Role::firstOrCreate(['name' => 'Student']);
    $accountant = Role::firstOrCreate(['name' => 'Accountant']);

    // Assign permissions
    $admin->givePermissionTo(Permission::all());

    $teacher->givePermissionTo([
        'view students',
        'view classes',
        'manage attendance',
    ]);

    $student->givePermissionTo([
        'view classes',
    ]);

    $accountant->givePermissionTo([
        'manage fees',
        'view payments',
    ]);
    }
}
