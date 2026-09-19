<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $diameter = Attribute::query()->updateOrCreate(['slug' => 'diameter'], [
            'name' => 'Diameter', 'name_ar' => 'القطر', 'type' => 'select',
        ]);
        foreach (['20mm', '25mm', '32mm', '40mm', '50mm', '63mm', '75mm', '110mm'] as $i => $v) {
            $diameter->values()->updateOrCreate(['value' => $v], ['value_ar' => $v, 'sort_order' => $i]);
        }

        $pressure = Attribute::query()->updateOrCreate(['slug' => 'pressure-rating'], [
            'name' => 'Pressure Rating', 'name_ar' => 'تصنيف الضغط', 'type' => 'select',
        ]);
        foreach (['PN10', 'PN16', 'PN20', 'PN25'] as $i => $v) {
            $pressure->values()->updateOrCreate(['value' => $v], ['value_ar' => $v, 'sort_order' => $i]);
        }

        $color = Attribute::query()->updateOrCreate(['slug' => 'color'], [
            'name' => 'Color', 'name_ar' => 'اللون', 'type' => 'color',
        ]);
        foreach ([['White', 'أبيض'], ['Green', 'أخضر'], ['Grey', 'رمادي']] as $i => $v) {
            $color->values()->updateOrCreate(['value' => $v[0]], ['value_ar' => $v[1], 'sort_order' => $i]);
        }

        $material = Attribute::query()->updateOrCreate(['slug' => 'material'], [
            'name' => 'Material', 'name_ar' => 'المادة', 'type' => 'select',
        ]);
        foreach ([['PPR', 'PPR'], ['PPR-FR (Fiber Reinforced)', 'PPR-FR (مقوى بالألياف)'], ['PP-R/AL/PP-R', 'PP-R/AL/PP-R']] as $i => $v) {
            $material->values()->updateOrCreate(['value' => $v[0]], ['value_ar' => $v[1], 'sort_order' => $i]);
        }
    }
}
