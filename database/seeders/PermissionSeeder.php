<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard' => ['view'],
            'users' => ['view', 'create', 'update', 'manage'],
            'roles' => ['view', 'create', 'update', 'delete', 'manage'],
            'branches' => ['view', 'create', 'update', 'manage', 'view_all'],
            'customers' => ['view', 'create', 'update', 'delete', 'export', 'import', 'blacklist'],
            'pets' => ['view', 'create', 'update', 'delete'],
            'products' => ['view', 'create', 'update', 'delete', 'import', 'export'],
            'inventory' => ['view', 'adjust', 'opname', 'transfer', 'approve_transfer'],
            'warehouses' => ['manage'],
            'pos' => ['view', 'create', 'discount', 'void', 'refund', 'approve', 'cash_manage', 'cash_close', 'print'],
            'purchasing' => ['view', 'create', 'approve', 'receive', 'pay'],
            'suppliers' => ['view', 'create', 'update', 'delete'],
            'grooming' => ['view', 'create', 'update', 'assign', 'checkin', 'qc', 'pay', 'manage'],
            'bookings' => ['view', 'create', 'update', 'confirm', 'cancel'],
            'veterinary' => ['view', 'create', 'update', 'manage'],
            'medical_records' => ['view', 'create', 'update', 'finalize', 'export'],
            'pharmacy' => ['view', 'manage', 'dispense'],
            'prescriptions' => ['create'],
            'loyalty' => ['view', 'adjust', 'manage'],
            'promotions' => ['view', 'create', 'update', 'manage', 'approve'],
            'staff' => ['view', 'create', 'update', 'manage'],
            'finance' => ['view', 'expense_create', 'expense_approve', 'reconcile'],
            'reports' => ['view', 'export'],
            'notifications' => ['manage_templates'],
            'settings' => ['view', 'manage'],
            'audit' => ['view'],
            'integrations' => ['view', 'manage', 'test'],
            'backup' => ['manage'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }
    }
}
