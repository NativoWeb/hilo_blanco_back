<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'users.manage',
            'settings.view', 'settings.manage',
            'tokens.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // El rol de mayor jerarquía en este proyecto es super_admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'admin'])->syncPermissions([
            'products.view', 'products.create', 'products.update',
            'categories.view', 'categories.create', 'categories.update',
            'settings.view',
        ]);

        $user = User::firstOrCreate(
            ['email' => 'admin@hiloblanco.com'],
            [
                'name'     => 'Administrador',
                'password' => bcrypt('HiloBlanco2024!'),
                'status'   => 1,
            ]
        );

        // Actualizar contraseña en caso de que el usuario ya existiera sin hash
        $user->update(['password' => bcrypt('HiloBlanco2024!')]);

        $user->syncRoles(['super_admin']);
    }
}
