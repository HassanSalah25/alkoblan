<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    protected array $diameters = ['20mm', '25mm', '32mm', '40mm', '50mm', '63mm', '75mm', '110mm'];

    public function run(): void
    {
        $this->seedPipes();
        $this->seedFittings();
        $this->seedValves();
        $this->seedAccessories();
        $this->seedHdpeAndInsulation();
    }

    protected function attachImage(Product $product, string $filename): void
    {
        $media = Media::where('path', 'site/'.$filename)->first();
        if ($media) {
            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'media_id' => $media->id, 'type' => 'main'],
                ['sort_order' => 0]
            );
        }
    }

    protected function makeProduct(array $data): Product
    {
        return Product::query()->updateOrCreate(['slug' => $data['slug']], $data);
    }

    protected function seedPipes(): void
    {
        $thermo = ProductCategory::where('slug', 'ktp-thermopipes')->first();
        $multilayer = ProductCategory::where('slug', 'ktp-multilayer-pipes')->first();
        $diameterValues = AttributeValue::whereHas('attribute', fn ($q) => $q->where('slug', 'diameter'))->get()->keyBy('value');
        $pressureValues = AttributeValue::whereHas('attribute', fn ($q) => $q->where('slug', 'pressure-rating'))->get()->keyBy('value');

        $lines = [
            ['name' => 'KTP PPR Hot & Cold Water Pipe PN20', 'name_ar' => 'ماسورة KTP PPR للمياه الساخنة والبارة PN20', 'cat' => $thermo, 'pressure' => 'PN20', 'price' => 18],
            ['name' => 'KTP PPR Hot & Cold Water Pipe PN25', 'name_ar' => 'ماسورة KTP PPR للمياه الساخنة والبارة PN25', 'cat' => $thermo, 'pressure' => 'PN25', 'price' => 22],
            ['name' => 'KTP PPR Cold Water Pipe PN10', 'name_ar' => 'ماسورة KTP PPR للمياه البارة PN10', 'cat' => $thermo, 'pressure' => 'PN10', 'price' => 14],
            ['name' => 'KTP Multilayer PERT/AL/PERT Pipe PN16', 'name_ar' => 'ماسورة KTP متعددة الطبقات PERT/AL/PERT PN16', 'cat' => $multilayer, 'pressure' => 'PN16', 'price' => 26],
            ['name' => 'KTP Multilayer Composite Pipe PN20', 'name_ar' => 'ماسورة KTP المركبة متعددة الطبقات PN20', 'cat' => $multilayer, 'pressure' => 'PN20', 'price' => 30],
        ];

        foreach ($lines as $i => $line) {
            $slug = Str::slug($line['name']);
            $product = $this->makeProduct([
                'category_id' => $line['cat']->id,
                'name' => $line['name'],
                'name_ar' => $line['name_ar'],
                'slug' => $slug,
                'sku' => 'PIPE-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'short_description' => 'High-quality thermal water pipe manufactured to international standards.',
                'short_description_ar' => 'ماسورة مياه حرارية عالية الجودة مصنعة وفق أعلى المعايير العالمية.',
                'description' => 'AL-KOBLAN pipes are manufactured from premium raw materials to guarantee durability, corrosion resistance and long service life for both hot and cold water plumbing systems.',
                'description_ar' => 'تُصنع مواسير الكبلان من مواد خام ممتازة لضمان المتانة ومقاومة التآكل وعمر افتراضي طويل لأنظمة السباكة الساخنة والبارة.',
                'technical_specifications' => [
                    ['label' => 'Pressure Rating', 'label_ar' => 'تصنيف الضغط', 'value' => $line['pressure']],
                    ['label' => 'Material', 'label_ar' => 'المادة', 'value' => 'PPR'],
                    ['label' => 'Max Temperature', 'label_ar' => 'أقصى درجة حرارة', 'value' => '95°C'],
                    ['label' => 'Standard', 'label_ar' => 'المعيار', 'value' => 'DIN 8077/8078'],
                    ['label' => 'Color', 'label_ar' => 'اللون', 'value' => 'Green / White'],
                    ['label' => 'Length', 'label_ar' => 'الطول', 'value' => '4m / coil'],
                ],
                'price' => $line['price'],
                'sale_price' => null,
                'stock_quantity' => 500,
                'stock_status' => 'in_stock',
                'is_featured' => $i < 2,
                'is_active' => true,
                'sort_order' => $i,
                'seo_title' => $line['name'],
                'seo_description' => $line['short_description'] ?? null,
            ]);
            $this->attachImage($product, 'pipe_product.jpg');

            if (isset($pressureValues[$line['pressure']])) {
                $product->attributeValues()->syncWithoutDetaching([$pressureValues[$line['pressure']]->id]);
            }

            foreach ($this->diameters as $vi => $diameter) {
                if (! isset($diameterValues[$diameter])) {
                    continue;
                }
                $variant = ProductVariant::query()->updateOrCreate(
                    ['product_id' => $product->id, 'sku' => $product->sku.'-'.str_replace('mm', '', $diameter)],
                    [
                        'price' => round($line['price'] * (1 + $vi * 0.35), 2),
                        'stock_quantity' => 200 - $vi * 10,
                        'is_active' => true,
                    ]
                );
                $variant->attributeValues()->syncWithoutDetaching([$diameterValues[$diameter]->id]);
            }
        }
    }

    protected function seedFittings(): void
    {
        $diameterValues = AttributeValue::whereHas('attribute', fn ($q) => $q->where('slug', 'diameter'))->get()->keyBy('value');

        $items = [
            ['elbows', 'PPR Elbow 90°', 'كوع PPR 90°', 6],
            ['elbows', 'PPR Elbow 45°', 'كوع PPR 45°', 5],
            ['tee', 'PPR Equal Tee', 'تي PPR متساوي', 7],
            ['tee', 'PPR Reducing Tee', 'تي PPR مختزل', 8],
            ['adapter', 'PPR Male Threaded Adapter', 'محول PPR ذكر بالبرغي', 6],
            ['adapter', 'PPR Female Threaded Adapter', 'محول PPR أنثى بالبرغي', 6],
            ['coupling', 'PPR Straight Coupling', 'وصلة مستقيمة PPR', 4],
            ['coupling', 'PPR Reducing Coupling', 'وصلة مستقيمة مختزلة PPR', 5],
            ['union-joint', 'PPR Union Joint', 'وصلة اتحاد PPR', 9],
            ['fittings-others', 'PPR End Cap', 'غطاء نهاية PPR', 3],
            ['fittings-others', 'PPR Cross Fitting', 'وصلة صليبية PPR', 10],
        ];

        foreach ($items as $i => [$catSlug, $name, $nameAr, $price]) {
            $cat = ProductCategory::where('slug', $catSlug)->first();
            $slug = Str::slug($name).'-'.($i + 1);
            $product = $this->makeProduct([
                'category_id' => $cat?->id,
                'name' => $name,
                'name_ar' => $nameAr,
                'slug' => $slug,
                'sku' => 'FIT-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'short_description' => 'Durable PPR fitting for reliable, leak-free plumbing connections.',
                'short_description_ar' => 'وصلة PPR متينة لتوصيلات سباكة موثوقة وخالية من التسرب.',
                'description' => 'Manufactured using fusion-welding grade PPR resin, tested for pressure integrity and long-term durability.',
                'description_ar' => 'مصنعة من راتنج PPR بجودة اللحام الحراري، ومختبرة لضمان سلامة الضغط والمتانة على المدى الطويل.',
                'technical_specifications' => [
                    ['label' => 'Material', 'label_ar' => 'المادة', 'value' => 'PPR'],
                    ['label' => 'Connection Type', 'label_ar' => 'نوع التوصيل', 'value' => 'Fusion Welding'],
                    ['label' => 'Standard', 'label_ar' => 'المعيار', 'value' => 'DIN 16962'],
                ],
                'price' => $price,
                'stock_quantity' => 1000,
                'stock_status' => 'in_stock',
                'is_featured' => $i < 3,
                'is_active' => true,
                'sort_order' => $i,
            ]);
            $this->attachImage($product, 'fitting_product.jpg');

            foreach (array_slice($this->diameters, 0, 6) as $diameter) {
                if (isset($diameterValues[$diameter])) {
                    $product->attributeValues()->syncWithoutDetaching([$diameterValues[$diameter]->id]);
                }
            }
        }
    }

    protected function seedValves(): void
    {
        $cat = ProductCategory::where('slug', 'ball-valves')->first();
        $diameterValues = AttributeValue::whereHas('attribute', fn ($q) => $q->where('slug', 'diameter'))->get()->keyBy('value');

        $items = [
            ['PPR Ball Valve', 'صمام كروي PPR', 35],
            ['PPR Check Valve', 'صمام فحص (لا رجعي) PPR', 40],
            ['PPR Gate Valve', 'صمام بوابة PPR', 45],
            ['Brass Ball Valve', 'صمام كروي نحاسي', 55],
        ];

        foreach ($items as $i => [$name, $nameAr, $price]) {
            $slug = Str::slug($name).'-'.($i + 1);
            $product = $this->makeProduct([
                'category_id' => $cat?->id,
                'name' => $name,
                'name_ar' => $nameAr,
                'slug' => $slug,
                'sku' => 'VLV-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'short_description' => 'Reliable shut-off valve for water distribution systems.',
                'short_description_ar' => 'صمام إغلاق موثوق لأنظمة توزيع المياه.',
                'description' => 'Engineered for smooth operation and a tight seal across the full pressure rating of the connected pipe system.',
                'description_ar' => 'مصمم لتشغيل سلس وإغلاق محكم عبر كامل تصنيف الضغط لنظام المواسير المتصل.',
                'technical_specifications' => [
                    ['label' => 'Material', 'label_ar' => 'المادة', 'value' => str_contains($name, 'Brass') ? 'Brass' : 'PPR'],
                    ['label' => 'Max Pressure', 'label_ar' => 'أقصى ضغط', 'value' => 'PN20'],
                ],
                'price' => $price,
                'stock_quantity' => 300,
                'stock_status' => 'in_stock',
                'is_featured' => $i === 0,
                'is_active' => true,
                'sort_order' => $i,
            ]);
            $this->attachImage($product, 'valve_product.jpg');

            foreach (array_slice($this->diameters, 0, 5) as $diameter) {
                if (isset($diameterValues[$diameter])) {
                    $product->attributeValues()->syncWithoutDetaching([$diameterValues[$diameter]->id]);
                }
            }
        }
    }

    protected function seedAccessories(): void
    {
        $cat = ProductCategory::where('slug', 'accessories-general')->first();

        $items = [
            ['PPR Fusion Welding Machine', 'ماكينة لحام PPR حرارية', 850],
            ['Pipe Clamp Set', 'طقم مشابك تثبيت المواسير', 25],
            ['PPR Pipe Cutter', 'قاطع مواسير PPR', 60],
            ['Wall Mounting Bracket', 'حامل تثبيت على الحائط', 15],
        ];

        foreach ($items as $i => [$name, $nameAr, $price]) {
            $slug = Str::slug($name).'-'.($i + 1);
            $product = $this->makeProduct([
                'category_id' => $cat?->id,
                'name' => $name,
                'name_ar' => $nameAr,
                'slug' => $slug,
                'sku' => 'ACC-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'short_description' => 'Installation and maintenance accessory for professional plumbing work.',
                'short_description_ar' => 'ملحق تركيب وصيانة لأعمال السباكة الاحترافية.',
                'price' => $price,
                'stock_quantity' => 150,
                'stock_status' => 'in_stock',
                'is_active' => true,
                'sort_order' => $i,
            ]);
            $this->attachImage($product, 'accessories_product.jpg');
        }
    }

    protected function seedHdpeAndInsulation(): void
    {
        $hdpe = ProductCategory::where('slug', 'hdpe')->first();
        $insulation = ProductCategory::where('slug', 'insulation')->first();

        $product = $this->makeProduct([
            'category_id' => $hdpe?->id,
            'name' => 'HDPE Water Pipe PE100',
            'name_ar' => 'ماسورة HDPE للمياه PE100',
            'slug' => 'hdpe-water-pipe-pe100',
            'sku' => 'HDPE-0001',
            'short_description' => 'High-density polyethylene pipe for water distribution and infrastructure projects.',
            'short_description_ar' => 'ماسورة بولي إيثيلين عالية الكثافة لتوزيع المياه ومشاريع البنية التحتية.',
            'price' => 40,
            'stock_quantity' => 400,
            'is_featured' => true,
            'is_active' => true,
        ]);
        $this->attachImage($product, 'pipe_product.jpg');

        $product2 = $this->makeProduct([
            'category_id' => $insulation?->id,
            'name' => 'Thermal Pipe Insulation Sleeve',
            'name_ar' => 'كم عزل حراري للمواسير',
            'slug' => 'thermal-pipe-insulation-sleeve',
            'sku' => 'INS-0001',
            'short_description' => 'Foam insulation sleeve to reduce heat loss and prevent condensation.',
            'short_description_ar' => 'كم عزل رغوي لتقليل فقد الحرارة ومنع التكثف.',
            'price' => 12,
            'stock_quantity' => 600,
            'is_active' => true,
        ]);
        $this->attachImage($product2, 'accessories_product.jpg');
    }
}
