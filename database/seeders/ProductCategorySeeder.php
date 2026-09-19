<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $pipe = ProductCategory::query()->updateOrCreate(['slug' => 'pipes'], [
            'name' => 'Pipes', 'name_ar' => 'المواسير',
            'description' => 'PPR and thermoplastic water pipes for hot and cold water systems.',
            'description_ar' => 'مواسير المياه الحرارية والبلاستيكية للمياه الساخنة والبارة.',
            'icon' => 'bi-water', 'sort_order' => 1, 'is_featured' => true, 'is_active' => true,
        ]);
        ProductCategory::query()->updateOrCreate(['slug' => 'ktp-thermopipes'], [
            'parent_id' => $pipe->id, 'name' => 'KTP Thermopipes', 'name_ar' => 'مواسير KTP الحرارية',
            'description_ar' => 'مواسير PPR حرارية عالية الجودة لتوزيع المياه الساخنة والبارة.',
            'sort_order' => 1, 'is_active' => true,
        ]);
        ProductCategory::query()->updateOrCreate(['slug' => 'ktp-multilayer-pipes'], [
            'parent_id' => $pipe->id, 'name' => 'KTP Multilayer Pipes', 'name_ar' => 'مواسير KTP متعددة الطبقات',
            'description_ar' => 'مواسير متعددة الطبقات (PERT/AL/PERT) لمرونة وعمر افتراضي أطول.',
            'sort_order' => 2, 'is_active' => true,
        ]);

        $fitting = ProductCategory::query()->updateOrCreate(['slug' => 'fittings'], [
            'name' => 'Fittings', 'name_ar' => 'الوصلات',
            'description_ar' => 'وصلات وتفريعات PPR لجميع تطبيقات السباكة.',
            'icon' => 'bi-puzzle-fill', 'sort_order' => 2, 'is_featured' => true, 'is_active' => true,
        ]);
        foreach ([
            ['elbows', 'Elbows', 'الكوعات'],
            ['tee', 'Tee', 'التيات'],
            ['adapter', 'Adapter', 'المحولات'],
            ['coupling', 'Coupling', 'الوصلات المستقيمة'],
            ['union-joint', 'Union Joint', 'وصلة الاتحاد'],
            ['fittings-others', 'Others', 'أخرى'],
        ] as $i => $sub) {
            ProductCategory::query()->updateOrCreate(['slug' => $sub[0]], [
                'parent_id' => $fitting->id, 'name' => $sub[1], 'name_ar' => $sub[2],
                'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }

        $valves = ProductCategory::query()->updateOrCreate(['slug' => 'valves'], [
            'name' => 'Valves', 'name_ar' => 'الصمامات',
            'description_ar' => 'صمامات كروية وصمامات تحكم بمعايير الجودة العالمية.',
            'icon' => 'bi-droplet-fill', 'sort_order' => 3, 'is_featured' => true, 'is_active' => true,
        ]);
        ProductCategory::query()->updateOrCreate(['slug' => 'ball-valves'], [
            'parent_id' => $valves->id, 'name' => 'Valves', 'name_ar' => 'الصمامات',
            'sort_order' => 1, 'is_active' => true,
        ]);

        $accessories = ProductCategory::query()->updateOrCreate(['slug' => 'accessories'], [
            'name' => 'Accessories', 'name_ar' => 'الملحقات',
            'description_ar' => 'ملحقات ومستلزمات التركيب والصيانة.',
            'icon' => 'bi-tools', 'sort_order' => 4, 'is_featured' => true, 'is_active' => true,
        ]);
        ProductCategory::query()->updateOrCreate(['slug' => 'accessories-general'], [
            'parent_id' => $accessories->id, 'name' => 'Accessories', 'name_ar' => 'الملحقات',
            'sort_order' => 1, 'is_active' => true,
        ]);

        ProductCategory::query()->updateOrCreate(['slug' => 'hdpe'], [
            'name' => 'HDPE Pipes', 'name_ar' => 'مواسير HDPE',
            'description_ar' => 'مواسير البولي إيثيلين عالية الكثافة لخطوط المياه ومشاريع الصرف.',
            'icon' => 'bi-water', 'sort_order' => 5, 'is_active' => true,
        ]);
        ProductCategory::query()->updateOrCreate(['slug' => 'insulation'], [
            'name' => 'Thermal Insulation', 'name_ar' => 'عوازل حرارية',
            'description_ar' => 'عوازل حرارية للمواسير للحفاظ على درجة حرارة المياه وتقليل الفقد.',
            'icon' => 'bi-thermometer-snow', 'sort_order' => 6, 'is_active' => true,
        ]);
    }
}
