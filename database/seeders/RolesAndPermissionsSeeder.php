<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    protected array $modules = [
        'pages', 'homepage', 'menus', 'testimonials', 'faqs', 'blogs', 'events', 'careers',
        'products', 'categories', 'attributes', 'product_files',
        'orders', 'customers', 'branches', 'media', 'contact_messages',
        'settings', 'users', 'roles',
    ];

    public function run(): void
    {
        foreach ($this->modules as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }
        Permission::firstOrCreate(['name' => 'settings.manage', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.manage', 'guard_name' => 'web']);

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::where('name', 'not like', 'users.%')
            ->where('name', 'not like', 'roles.%')
            ->get());

        $contentManager = Role::firstOrCreate(['name' => 'Content Manager', 'guard_name' => 'web']);
        $contentManager->syncPermissions(
            Permission::where(function ($q) {
                $q->where('name', 'like', 'pages.%')
                    ->orWhere('name', 'like', 'homepage.%')
                    ->orWhere('name', 'like', 'menus.%')
                    ->orWhere('name', 'like', 'testimonials.%')
                    ->orWhere('name', 'like', 'faqs.%')
                    ->orWhere('name', 'like', 'blogs.%')
                    ->orWhere('name', 'like', 'events.%')
                    ->orWhere('name', 'like', 'careers.%')
                    ->orWhere('name', 'like', 'media.%');
            })->get()
        );

        $productManager = Role::firstOrCreate(['name' => 'Product Manager', 'guard_name' => 'web']);
        $productManager->syncPermissions(
            Permission::where(function ($q) {
                $q->where('name', 'like', 'products.%')
                    ->orWhere('name', 'like', 'categories.%')
                    ->orWhere('name', 'like', 'attributes.%')
                    ->orWhere('name', 'like', 'product_files.%')
                    ->orWhere('name', 'like', 'orders.%')
                    ->orWhere('name', 'like', 'media.%');
            })->get()
        );

        $editor = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $editor->syncPermissions(
            Permission::whereIn('name', [
                'pages.view', 'pages.create', 'pages.update',
                'blogs.view', 'blogs.create', 'blogs.update',
                'faqs.view', 'faqs.create', 'faqs.update',
                'media.view', 'media.create',
            ])->get()
        );

        Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
    }
}
