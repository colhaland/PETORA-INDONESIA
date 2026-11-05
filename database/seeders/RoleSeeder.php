<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Super Admin', 'Admin', 'Dokter', 'Customer'] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $super = Role::findByName('Super Admin');
        $super->syncPermissions(Permission::all());

        $admin = Role::findByName('Admin');
        $admin->syncPermissions(Permission::where('name', 'not like', 'integrations.%')
            ->where('name', 'not like', 'backup.%')
            ->where('name', 'not like', 'settings.manage')
            ->get());

        $dokter = Role::findByName('Dokter');
        $dokter->syncPermissions([
            'dashboard.view', 'pets.view', 'veterinary.view', 'veterinary.create', 'veterinary.update',
            'medical_records.view', 'medical_records.create', 'medical_records.update', 'medical_records.finalize',
            'pharmacy.view', 'prescriptions.create', 'customers.view',
        ]);

        $customer = Role::findByName('Customer');
        $customer->syncPermissions(['dashboard.view']);
    }
}
