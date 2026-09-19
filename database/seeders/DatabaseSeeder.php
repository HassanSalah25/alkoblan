<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
            MenuSeeder::class,
            BranchSeeder::class,
            MediaSeeder::class,
            ProductCategorySeeder::class,
            AttributeSeeder::class,
            ProductSeeder::class,
            HomepageCmsSeeder::class,
            FaqSeeder::class,
            BlogAndEventSeeder::class,
            CareerSeeder::class,
            PageSeeder::class,
        ]);
    }
}
