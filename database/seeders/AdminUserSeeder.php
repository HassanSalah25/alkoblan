<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@alkoblan.com.sa'],
            [
                'name' => 'Al-Koblan Super Admin',
                'password' => 'Password123!',
                'type' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['Super Admin']);
    }
}
