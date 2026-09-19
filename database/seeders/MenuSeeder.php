<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        MenuItem::query()->delete();

        $home = MenuItem::create(['location' => 'main', 'title' => 'Home', 'title_ar' => 'الرئيسية', 'url' => '/', 'sort_order' => 1]);

        // "About" dropdown — mirrors alkoblan.com.sa's own "COMPANY" nav item.
        $about = MenuItem::create(['location' => 'main', 'title' => 'About', 'title_ar' => 'من نحن', 'url' => '/about', 'sort_order' => 2]);
        MenuItem::create(['location' => 'main', 'parent_id' => $about->id, 'title' => 'Mission & Vision', 'title_ar' => 'الرسالة والرؤية', 'url' => '/pages/mission-vision', 'icon' => 'bi-bullseye', 'sort_order' => 1]);
        MenuItem::create(['location' => 'main', 'parent_id' => $about->id, 'title' => 'Innovation', 'title_ar' => 'الابتكار', 'url' => '/pages/innovation', 'icon' => 'bi-lightbulb', 'sort_order' => 2]);
        MenuItem::create(['location' => 'main', 'parent_id' => $about->id, 'title' => 'Blog', 'title_ar' => 'المدونة', 'url' => '/blog', 'icon' => 'bi-newspaper', 'sort_order' => 3]);
        MenuItem::create(['location' => 'main', 'parent_id' => $about->id, 'title' => 'Events', 'title_ar' => 'الفعاليات', 'url' => '/events', 'icon' => 'bi-calendar3', 'sort_order' => 4]);
        MenuItem::create(['location' => 'main', 'parent_id' => $about->id, 'title' => 'FAQs', 'title_ar' => 'الأسئلة الشائعة', 'url' => '/faq', 'icon' => 'bi-question-circle', 'sort_order' => 5]);

        // "Quality" dropdown.
        $quality = MenuItem::create(['location' => 'main', 'title' => 'Quality', 'title_ar' => 'الجودة', 'url' => '/pages/quality', 'sort_order' => 3]);
        MenuItem::create(['location' => 'main', 'parent_id' => $quality->id, 'title' => 'Quality', 'title_ar' => 'الجودة', 'url' => '/pages/quality', 'icon' => 'bi-award', 'sort_order' => 1]);
        MenuItem::create(['location' => 'main', 'parent_id' => $quality->id, 'title' => 'Raw Materials', 'title_ar' => 'المواد الخام', 'url' => '/pages/raw-materials', 'icon' => 'bi-box-seam', 'sort_order' => 2]);
        MenuItem::create(['location' => 'main', 'parent_id' => $quality->id, 'title' => 'Quality Test', 'title_ar' => 'اختبار الجودة', 'url' => '/pages/quality-test', 'icon' => 'bi-clipboard-check', 'sort_order' => 3]);
        MenuItem::create(['location' => 'main', 'parent_id' => $quality->id, 'title' => 'Installation Tests', 'title_ar' => 'اختبارات التركيب', 'url' => '/pages/installation-tests', 'icon' => 'bi-tools', 'sort_order' => 4]);
        MenuItem::create(['location' => 'main', 'parent_id' => $quality->id, 'title' => 'Research & Development', 'title_ar' => 'البحث والتطوير', 'url' => '/pages/research-development', 'icon' => 'bi-flask', 'sort_order' => 5]);
        MenuItem::create(['location' => 'main', 'parent_id' => $quality->id, 'title' => 'Quality Certificates', 'title_ar' => 'شهادات الجودة', 'url' => '/pages/quality-certificates', 'icon' => 'bi-patch-check', 'sort_order' => 6]);

        $products = MenuItem::create(['location' => 'main', 'title' => 'Products', 'title_ar' => 'المنتجات', 'url' => '/categories', 'sort_order' => 4]);
        MenuItem::create(['location' => 'main', 'parent_id' => $products->id, 'title' => 'Categories', 'title_ar' => 'الفئات', 'url' => '/categories', 'icon' => 'bi-grid-3x3-gap-fill', 'sort_order' => 1]);
        MenuItem::create(['location' => 'main', 'parent_id' => $products->id, 'title' => 'Shop', 'title_ar' => 'المتجر', 'url' => '/shop', 'icon' => 'bi-shop', 'sort_order' => 2]);
        MenuItem::create(['location' => 'main', 'parent_id' => $products->id, 'title' => 'Pipes', 'title_ar' => 'المواسير', 'url' => '/shop?cat=pipes', 'icon' => 'bi-water', 'sort_order' => 3]);
        MenuItem::create(['location' => 'main', 'parent_id' => $products->id, 'title' => 'Fittings', 'title_ar' => 'الوصلات', 'url' => '/shop?cat=fittings', 'icon' => 'bi-puzzle-fill', 'sort_order' => 4]);
        MenuItem::create(['location' => 'main', 'parent_id' => $products->id, 'title' => 'Valves', 'title_ar' => 'الصمامات', 'url' => '/shop?cat=valves', 'icon' => 'bi-droplet-fill', 'sort_order' => 5]);
        MenuItem::create(['location' => 'main', 'parent_id' => $products->id, 'title' => 'Accessories', 'title_ar' => 'الملحقات', 'url' => '/shop?cat=accessories', 'icon' => 'bi-tools', 'sort_order' => 6]);

        // "التعليمات" (Guidelines) dropdown.
        $guidelines = MenuItem::create(['location' => 'main', 'title' => 'Guidelines', 'title_ar' => 'التعليمات', 'url' => '/pages/piping-guidelines', 'sort_order' => 5]);
        MenuItem::create(['location' => 'main', 'parent_id' => $guidelines->id, 'title' => 'Piping Guidelines', 'title_ar' => 'تعليمات مد الأنابيب', 'url' => '/pages/piping-guidelines', 'icon' => 'bi-diagram-3', 'sort_order' => 1]);

        // "الخدمات" (Services) dropdown.
        $services = MenuItem::create(['location' => 'main', 'title' => 'Services', 'title_ar' => 'الخدمات', 'url' => '#', 'sort_order' => 6]);
        MenuItem::create(['location' => 'main', 'parent_id' => $services->id, 'title' => 'Free Supervision', 'title_ar' => 'الإشراف المجاني', 'url' => '/pages/free-supervision', 'icon' => 'bi-person-check', 'sort_order' => 1]);
        MenuItem::create(['location' => 'main', 'parent_id' => $services->id, 'title' => 'Our Guarantee', 'title_ar' => 'ضماننا', 'url' => '/pages/our-guarantee', 'icon' => 'bi-shield-check', 'sort_order' => 2]);

        // Standalone link (no dropdown).
        MenuItem::create(['location' => 'main', 'title' => 'Social Responsibility', 'title_ar' => 'المسؤولية الاجتماعية', 'url' => '/pages/social-responsibility', 'sort_order' => 7]);

        MenuItem::create(['location' => 'main', 'title' => 'Contact Us', 'title_ar' => 'تواصل معنا', 'url' => '/contact', 'sort_order' => 8]);

        // footer quick links
        foreach ([
            ['Home', 'الرئيسية', '/'],
            ['About Us', 'من نحن', '/about'],
            ['Company Profile', 'ملف الشركة', '/company-profile'],
            ['Shop', 'المتجر', '/shop'],
            ['Categories', 'الفئات', '/categories'],
            ['Blog', 'المدونة', '/blog'],
            ['Contact Us', 'تواصل معنا', '/contact'],
        ] as $i => $item) {
            MenuItem::create(['location' => 'footer_quick', 'title' => $item[0], 'title_ar' => $item[1], 'url' => $item[2], 'sort_order' => $i + 1]);
        }

        // footer product links
        foreach ([
            ['PPR Pipes', 'مواسير PPR', '/shop?cat=pipes'],
            ['Fittings & Valves', 'وصلات ومحابس', '/shop?cat=fittings'],
            ['Valves', 'صمامات', '/shop?cat=valves'],
            ['Accessories', 'ملحقات', '/shop?cat=accessories'],
        ] as $i => $item) {
            MenuItem::create(['location' => 'footer_products', 'title' => $item[0], 'title_ar' => $item[1], 'url' => $item[2], 'sort_order' => $i + 1]);
        }

        // footer bottom
        foreach ([
            ['Privacy Policy', 'سياسة الخصوصية', '/pages/privacy-policy'],
            ['Terms & Conditions', 'الشروط والأحكام', '/pages/terms-and-conditions'],
            ['Sitemap', 'خريطة الموقع', '/sitemap.xml'],
        ] as $i => $item) {
            MenuItem::create(['location' => 'footer_bottom', 'title' => $item[0], 'title_ar' => $item[1], 'url' => $item[2], 'sort_order' => $i + 1]);
        }
    }
}
