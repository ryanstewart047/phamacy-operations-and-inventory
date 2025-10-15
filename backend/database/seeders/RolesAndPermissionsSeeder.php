<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // User & role management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.block',
            'roles.view',
            'roles.assign',
            'roles.manage',

            // Product & inventory
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'inventory.view',
            'inventory.adjust',
            'inventory.receive',
            'inventory.transfer',

            // Sales & POS
            'sales.view',
            'sales.create',
            'sales.refund',
            'sales.void',
            'sales.export',

            // Purchase orders & suppliers
            'suppliers.view',
            'suppliers.manage',
            'purchase-orders.view',
            'purchase-orders.manage',

            // Reports & finance
            'reports.view',
            'finance.expenses.manage',
            'finance.payments.manage',

            // System
            'system.audit.view',
            'system.settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission], ['guard_name' => 'web']);
        }

        $roles = [
            'super-admin' => $permissions,
            'pharmacist' => [
                'products.view',
                'inventory.view',
                'inventory.adjust',
                'sales.view',
                'sales.create',
                'sales.refund',
                'reports.view',
            ],
            'inventory-clerk' => [
                'products.view',
                'products.create',
                'products.update',
                'inventory.view',
                'inventory.receive',
                'inventory.transfer',
                'purchase-orders.view',
                'purchase-orders.manage',
            ],
            'cashier' => [
                'sales.view',
                'sales.create',
                'sales.void',
                'sales.export',
                'finance.payments.manage',
            ],
            'auditor' => [
                'reports.view',
                'system.audit.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName], ['guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@pharmacy.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'status' => 'active',
                'force_password_reset' => true,
                'password_changed_at' => null,
                'first_login_at' => null,
            ]
        );

        $admin->assignRole('super-admin');
    }
}
