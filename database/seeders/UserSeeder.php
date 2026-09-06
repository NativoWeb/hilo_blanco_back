<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@hiloblanco.com'],
            [
                'name' => 'HiloBlanco Admin',
                'password' => 'H1loBlanco#2024',
                'status' => 1,
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        $admin = User::firstOrCreate(
            ['email' => 'operaciones@hiloblanco.com'],
            [
                'name' => 'Operaciones HiloBlanco',
                'password' => 'Operaciones#2024',
                'status' => 1,
            ]
        );
        $admin->syncRoles(['admin']);
    }
}
