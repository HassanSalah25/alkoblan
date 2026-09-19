<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Riyadh - Head Office & Factory',
                'name_ar' => 'الرياض - المقر الرئيسي والمصنع',
                'city' => 'Riyadh', 'city_ar' => 'الرياض',
                'address' => 'Second Industrial City, Riyadh, Saudi Arabia',
                'address_ar' => 'المنطقة الصناعية الثانية، الرياض، المملكة العربية السعودية',
                'phone' => '+966 11 234 5678', 'phone_secondary' => '+966 55 000 0000',
                'email' => 'riyadh@alkoblan.com.sa',
                'latitude' => 24.6408, 'longitude' => 46.7728,
                'maps_url' => 'https://maps.google.com/?q=24.6408,46.7728',
                'working_hours' => 'Sunday - Thursday, 8:00 AM - 5:00 PM',
                'working_hours_ar' => 'الأحد - الخميس، 8:00 ص - 5:00 م',
                'sort_order' => 1,
            ],
            [
                'name' => 'Jeddah Branch',
                'name_ar' => 'فرع جدة',
                'city' => 'Jeddah', 'city_ar' => 'جدة',
                'address' => 'Industrial Area, Jeddah, Saudi Arabia',
                'address_ar' => 'المنطقة الصناعية، جدة، المملكة العربية السعودية',
                'phone' => '+966 12 234 5678', 'phone_secondary' => null,
                'email' => 'jeddah@alkoblan.com.sa',
                'latitude' => 21.5433, 'longitude' => 39.1728,
                'maps_url' => 'https://maps.google.com/?q=21.5433,39.1728',
                'working_hours' => 'Sunday - Thursday, 8:00 AM - 5:00 PM',
                'working_hours_ar' => 'الأحد - الخميس، 8:00 ص - 5:00 م',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dammam Branch',
                'name_ar' => 'فرع الدمام',
                'city' => 'Dammam', 'city_ar' => 'الدمام',
                'address' => 'Industrial Area, Dammam, Saudi Arabia',
                'address_ar' => 'المنطقة الصناعية، الدمام، المملكة العربية السعودية',
                'phone' => '+966 13 234 5678', 'phone_secondary' => null,
                'email' => 'dammam@alkoblan.com.sa',
                'latitude' => 26.4207, 'longitude' => 50.0888,
                'maps_url' => 'https://maps.google.com/?q=26.4207,50.0888',
                'working_hours' => 'Sunday - Thursday, 8:00 AM - 5:00 PM',
                'working_hours_ar' => 'الأحد - الخميس، 8:00 ص - 5:00 م',
                'sort_order' => 3,
            ],
        ];

        foreach ($branches as $b) {
            Branch::query()->updateOrCreate(['name' => $b['name']], $b);
        }
    }
}
