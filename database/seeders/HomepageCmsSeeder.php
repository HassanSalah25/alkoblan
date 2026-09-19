<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\FamousClient;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class HomepageCmsSeeder extends Seeder
{
    public function run(): void
    {
        $heroBanner = Media::where('path', 'site/hero_banner.jpg')->first();
        $factory = Media::where('path', 'site/factory_about.jpg')->first();

        HeroSlide::query()->updateOrCreate(['sort_order' => 0], [
            'tag' => 'Since 1995', 'tag_ar' => 'منذ عام 1995',
            'title' => 'AL-KOBLAN Thermopipe Factory', 'title_ar' => 'مصنع الكبلان للمواسير الحرارية',
            'subtitle' => 'The leading manufacturer of PPR water pipes, fittings and valves in Saudi Arabia.',
            'subtitle_ar' => 'الشركة الرائدة في تصنيع مواسير المياه الحرارية والوصلات والصمامات بالمملكة العربية السعودية.',
            'button_text' => 'Our Products', 'button_text_ar' => 'منتجاتنا', 'button_url' => '/shop',
            'button2_text' => 'Contact Us', 'button2_text_ar' => 'تواصل معنا', 'button2_url' => '/contact',
            'image_desktop_id' => $heroBanner?->id,
            'is_active' => true,
        ]);

        HeroSlide::query()->updateOrCreate(['sort_order' => 1], [
            'tag' => 'ISO 9001 Certified', 'tag_ar' => 'حاصلة على شهادة ISO 9001',
            'title' => '30+ Years of Manufacturing Excellence', 'title_ar' => '30+ عاماً من التميز في التصنيع',
            'subtitle' => 'International quality standards for every project, from residential to industrial.',
            'subtitle_ar' => 'معايير جودة عالمية لكل مشروع، من المشاريع السكنية إلى الصناعية.',
            'button_text' => 'About Us', 'button_text_ar' => 'من نحن', 'button_url' => '/about',
            'image_desktop_id' => $factory?->id,
            'is_active' => true,
        ]);

        $blocks = [
            [
                'key' => 'home_about', 'title' => 'Our Story', 'title_ar' => 'قصتنا',
                'subtitle' => '30+ Years of Experience', 'subtitle_ar' => '30+ سنة خبرة',
                'content' => 'Since 1995, AL-KOBLAN has been manufacturing premium thermal water pipes for the Saudi market, combining international quality standards with local manufacturing expertise.',
                'content_ar' => 'منذ عام 1995، يقوم مصنع الكبلان بتصنيع مواسير المياه الحرارية عالية الجودة للسوق السعودي، جامعاً بين معايير الجودة العالمية والخبرة التصنيعية المحلية.',
                'image_id' => $factory?->id,
                'button_text' => 'Read More', 'button_text_ar' => 'اقرأ المزيد', 'button_url' => '/about',
                'extra' => ['badge_number' => '30+', 'badge_label' => 'Years of Experience', 'badge_label_ar' => 'سنة خبرة'],
            ],
            [
                'key' => 'home_quality', 'title' => 'Uncompromising Quality', 'title_ar' => 'جودة لا تقبل التنازل',
                'content' => 'Every product is tested against international standards (SASO, ISO 9001) before it reaches our customers.',
                'content_ar' => 'يتم فحص كل منتج وفق المعايير العالمية (سابر، ISO 9001) قبل أن يصل إلى عملائنا.',
                'button_text' => 'Our Quality Standards', 'button_text_ar' => 'معايير الجودة لدينا', 'button_url' => '/company-profile#quality',
            ],
            [
                'key' => 'home_cta', 'title' => 'Need a Custom Quote?', 'title_ar' => 'تحتاج عرض سعر مخصص؟',
                'content' => 'Our engineering team is ready to support your next project with the right products and free technical supervision.',
                'content_ar' => 'فريقنا الهندسي جاهز لدعم مشروعك القادم بالمنتجات المناسبة والإشراف الفني المجاني.',
            ],
        ];
        foreach ($blocks as $b) {
            ContentBlock::query()->updateOrCreate(['key' => $b['key']], $b);
        }

        $testimonials = [
            ['name' => 'Mohammed Al-Otaibi', 'name_ar' => 'محمد العتيبي', 'position' => 'Project Manager', 'position_ar' => 'مدير مشروع', 'company' => 'Al-Bina Contracting', 'company_ar' => 'مقاولات البناء', 'content' => 'AL-KOBLAN pipes have never let us down on any project — consistent quality and on-time delivery every time.', 'content_ar' => 'مواسير الكبلان لم تخذلنا في أي مشروع - جودة ثابتة وتسليم في الوقت المحدد كل مرة.'],
            ['name' => 'Sarah Al-Harbi', 'name_ar' => 'سارة الحربي', 'position' => 'Procurement Manager', 'position_ar' => 'مديرة مشتريات', 'company' => 'Modern Homes Co.', 'company_ar' => 'شركة المنازل الحديثة', 'content' => 'Excellent technical support and a wide range of certified products for our residential developments.', 'content_ar' => 'دعم فني ممتاز ومجموعة واسعة من المنتجات المعتمدة لمشاريعنا السكنية.'],
            ['name' => 'Khalid Al-Dosari', 'name_ar' => 'خالد الدوسري', 'position' => 'Site Engineer', 'position_ar' => 'مهندس موقع', 'company' => 'Gulf Engineering', 'company_ar' => 'الخليج للاستشارات الهندسية', 'content' => 'The free installation supervision service saved us significant time and prevented costly mistakes.', 'content_ar' => 'خدمة الإشراف المجاني على التركيب وفرت علينا وقتاً كبيراً ومنعت أخطاء مكلفة.'],
        ];
        foreach ($testimonials as $i => $t) {
            Testimonial::query()->updateOrCreate(['name' => $t['name']], $t + ['rating' => 5, 'sort_order' => $i, 'is_active' => true]);
        }

        $clients = ['NEOM', 'Saudi Aramco', 'SABIC', 'Ministry of Water & Environment', 'Ministry of Housing'];
        foreach ($clients as $i => $c) {
            FamousClient::query()->updateOrCreate(['name' => $c], ['sort_order' => $i, 'is_active' => true]);
        }
    }
}
