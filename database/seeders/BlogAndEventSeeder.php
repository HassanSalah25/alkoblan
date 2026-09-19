<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Event;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogAndEventSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('type', 'admin')->first();
        $factory = Media::where('path', 'site/factory_about.jpg')->first();

        $news = BlogCategory::query()->updateOrCreate(['slug' => 'news'], ['name' => 'Company News', 'name_ar' => 'أخبار الشركة', 'sort_order' => 1]);
        $tips = BlogCategory::query()->updateOrCreate(['slug' => 'tips'], ['name' => 'Plumbing Tips', 'name_ar' => 'نصائح السباكة', 'sort_order' => 2]);
        $industry = BlogCategory::query()->updateOrCreate(['slug' => 'industry'], ['name' => 'Industry Insights', 'name_ar' => 'رؤى الصناعة', 'sort_order' => 3]);

        $tagPipes = BlogTag::query()->updateOrCreate(['slug' => 'pipes'], ['name' => 'Pipes', 'name_ar' => 'مواسير']);
        $tagQuality = BlogTag::query()->updateOrCreate(['slug' => 'quality'], ['name' => 'Quality', 'name_ar' => 'جودة']);

        $posts = [
            ['news', 'AL-KOBLAN Opens New Production Line in Riyadh', 'الكبلان تفتتح خط إنتاج جديد في الرياض', 'We are proud to announce the launch of our new multilayer pipe production line, doubling our manufacturing capacity.', 'يسرنا الإعلان عن تشغيل خط إنتاج جديد للمواسير متعددة الطبقات، مما يضاعف قدرتنا الإنتاجية.'],
            ['tips', 'How to Choose the Right Pipe Diameter for Your Project', 'كيف تختار قطر الماسورة المناسب لمشروعك', 'A practical guide to selecting the correct pipe diameter and pressure rating for residential and commercial plumbing.', 'دليل عملي لاختيار القطر وتصنيف الضغط الصحيح لأنظمة السباكة السكنية والتجارية.'],
            ['industry', 'Understanding PPR vs HDPE Pipes', 'الفرق بين مواسير PPR ومواسير HDPE', 'An overview of the key differences between PPR and HDPE piping systems and when to use each.', 'نظرة عامة على الفروقات الأساسية بين أنظمة مواسير PPR و HDPE ومتى تستخدم كل نوع.'],
            ['tips', '5 Common Installation Mistakes to Avoid', '5 أخطاء تركيب شائعة يجب تجنبها', 'Avoid these common fusion-welding and installation mistakes to extend the lifespan of your plumbing system.', 'تجنب أخطاء اللحام الحراري والتركيب الشائعة هذه لإطالة عمر نظام السباكة الخاص بك.'],
            ['news', 'AL-KOBLAN Achieves ISO 9001:2015 Recertification', 'الكبلان تحصل على إعادة اعتماد ISO 9001:2015', 'Our quality management system has been successfully recertified, reaffirming our commitment to international standards.', 'تم إعادة اعتماد نظام إدارة الجودة لدينا بنجاح، مما يؤكد التزامنا بالمعايير العالمية.'],
        ];

        foreach ($posts as $i => [$catSlug, $title, $titleAr, $excerpt, $excerptAr]) {
            $cat = BlogCategory::where('slug', $catSlug)->first();
            $post = BlogPost::query()->updateOrCreate(['slug' => \Illuminate\Support\Str::slug($title)], [
                'blog_category_id' => $cat->id,
                'user_id' => $author?->id,
                'title' => $title, 'title_ar' => $titleAr,
                'excerpt' => $excerpt, 'excerpt_ar' => $excerptAr,
                'content' => "<p>{$excerpt}</p><p>AL-KOBLAN continues to invest in quality, technology and customer service to serve the Saudi construction and plumbing sector.</p>",
                'content_ar' => "<p>{$excerptAr}</p><p>يستمر مصنع الكبلان في الاستثمار بالجودة والتقنية وخدمة العملاء لخدمة قطاع البناء والسباكة في المملكة.</p>",
                'featured_image_id' => $factory?->id,
                'status' => 'published',
                'published_at' => now()->subDays(($i + 1) * 5),
            ]);
            $post->tags()->syncWithoutDetaching([$tagPipes->id, $tagQuality->id]);
        }

        $events = [
            ['Saudi Build Exhibition 2026', 'معرض ساودي بيلد 2026', 'Riyadh International Convention & Exhibition Center', 'مركز الرياض الدولي للمؤتمرات والمعارض', now()->addMonths(2)],
            ['Plumbing & Water Technology Forum', 'منتدى تقنيات السباكة والمياه', 'Jeddah Superdome', 'جدة سوبر دوم', now()->addMonths(4)],
            ['AL-KOBLAN Factory Open Day', 'يوم مفتوح في مصنع الكبلان', 'Riyadh Factory - Second Industrial City', 'مصنع الرياض - المنطقة الصناعية الثانية', now()->subMonths(1)],
        ];

        foreach ($events as $i => [$title, $titleAr, $loc, $locAr, $date]) {
            Event::query()->updateOrCreate(['slug' => \Illuminate\Support\Str::slug($title)], [
                'title' => $title, 'title_ar' => $titleAr,
                'description' => 'Join AL-KOBLAN at this event to learn more about our latest products and manufacturing capabilities.',
                'description_ar' => 'انضم إلى الكبلان في هذه الفعالية لمعرفة المزيد عن أحدث منتجاتنا وقدراتنا التصنيعية.',
                'location' => $loc, 'location_ar' => $locAr,
                'event_date' => $date,
                'featured_image_id' => $factory?->id,
                'is_featured' => $i === 0,
                'status' => 'published',
            ]);
        }
    }
}
