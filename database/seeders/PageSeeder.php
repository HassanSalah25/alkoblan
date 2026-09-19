<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about', 'title' => 'About Us', 'title_ar' => 'من نحن',
                'seo_title' => 'About AL-KOBLAN Thermopipe Factory', 'seo_title_ar' => 'عن مصنع الكبلان للمواسير الحرارية',
                'seo_description' => 'Learn about AL-KOBLAN, the leading Saudi manufacturer of thermal water pipes since 1995.',
                'seo_description_ar' => 'تعرف على مصنع الكبلان، الشركة السعودية الرائدة في تصنيع مواسير المياه الحرارية منذ عام 1995.',
                'status' => 'published', 'published_at' => now(),
            ],
            [
                'slug' => 'company-profile', 'title' => 'Company Profile', 'title_ar' => 'ملف الشركة',
                'seo_title' => 'AL-KOBLAN Company Profile', 'seo_title_ar' => 'ملف شركة الكبلان',
                'seo_description' => 'Full corporate profile of AL-KOBLAN Thermopipe Factory: overview, quality, branches and contact.',
                'seo_description_ar' => 'الملف التعريفي الكامل لمصنع الكبلان للمواسير الحرارية: نظرة عامة، الجودة، الفروع والتواصل.',
                'status' => 'published', 'published_at' => now(),
            ],
            [
                'slug' => 'privacy-policy', 'title' => 'Privacy Policy', 'title_ar' => 'سياسة الخصوصية',
                'content' => '<p>AL-KOBLAN respects your privacy. This page explains how we collect, use and protect your personal information.</p>',
                'content_ar' => '<p>يحترم مصنع الكبلان خصوصيتك. توضح هذه الصفحة كيفية جمع واستخدام وحماية معلوماتك الشخصية.</p>',
                'status' => 'published', 'published_at' => now(),
            ],
            [
                'slug' => 'terms-and-conditions', 'title' => 'Terms & Conditions', 'title_ar' => 'الشروط والأحكام',
                'content' => '<p>By using this website and purchasing our products you agree to the following terms and conditions.</p>',
                'content_ar' => '<p>باستخدامك لهذا الموقع وشرائك لمنتجاتنا فإنك توافق على الشروط والأحكام التالية.</p>',
                'status' => 'published', 'published_at' => now(),
            ],
        ];

        foreach ($pages as $p) {
            Page::query()->updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
