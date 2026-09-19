<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // general
            ['key' => 'site_name', 'value' => 'AL-KOBLAN Thermopipe Factory', 'group' => 'general'],
            ['key' => 'site_name_ar', 'value' => 'مصنع الكبلان للمواسير الحرارية', 'group' => 'general'],
            ['key' => 'logo', 'value' => null, 'group' => 'general', 'type' => 'image'],
            ['key' => 'favicon', 'value' => null, 'group' => 'general', 'type' => 'image'],
            ['key' => 'default_language', 'value' => 'ar', 'group' => 'general'],
            ['key' => 'default_currency', 'value' => 'SAR', 'group' => 'general'],
            ['key' => 'founded_year', 'value' => '1995', 'group' => 'general'],

            // contact
            ['key' => 'contact_email', 'value' => 'info@alkoblan.com.sa', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+966 11 234 5678', 'group' => 'contact'],
            ['key' => 'contact_phone_secondary', 'value' => '+966 55 000 0000', 'group' => 'contact'],
            ['key' => 'contact_whatsapp', 'value' => '966500000000', 'group' => 'contact'],
            ['key' => 'contact_address', 'value' => 'Riyadh - Second Industrial City, Saudi Arabia', 'group' => 'contact'],
            ['key' => 'contact_address_ar', 'value' => 'الرياض - المنطقة الصناعية الثانية، المملكة العربية السعودية', 'group' => 'contact'],
            ['key' => 'working_hours', 'value' => 'Sunday - Thursday, 8:00 AM - 5:00 PM', 'group' => 'contact'],
            ['key' => 'working_hours_ar', 'value' => 'الأحد - الخميس، 8:00 ص - 5:00 م', 'group' => 'contact'],
            ['key' => 'google_maps_embed', 'value' => 'https://maps.google.com/maps?q=24.6408,46.7728&hl=ar&z=14&output=embed', 'group' => 'contact'],

            // social
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/alkoblan', 'group' => 'social'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/alkoblan', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/alkoblan', 'group' => 'social'],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/alkoblan', 'group' => 'social'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/@alkoblan', 'group' => 'social'],

            // seo
            ['key' => 'seo_default_title', 'value' => 'AL-KOBLAN Thermopipe | Leading Water Pipe Manufacturer in Saudi Arabia', 'group' => 'seo'],
            ['key' => 'seo_default_title_ar', 'value' => 'الكبلان ثيرموبايب | الرائد في تصنيع مواسير المياه بالسعودية', 'group' => 'seo'],
            ['key' => 'seo_default_description', 'value' => 'AL-KOBLAN is the leading manufacturer of PPR water pipes, fittings and valves in Saudi Arabia.', 'group' => 'seo'],
            ['key' => 'seo_default_description_ar', 'value' => 'الكبلان ثيرموبايب - الشركة الرائدة في تصنيع مواسير المياه بالمملكة العربية السعودية. نقدم مواسير PPR، وصلات، وصمامات وكل مستلزمات السباكة بأعلى جودة.', 'group' => 'seo'],
            ['key' => 'seo_default_keywords', 'value' => 'مواسير مياه, مواسير PPR, الكبلان, مواسير السعودية, سباكة, ثيرموبايب', 'group' => 'seo'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'seo'],
            ['key' => 'google_tag_manager_id', 'value' => '', 'group' => 'seo'],

            // footer
            ['key' => 'footer_description', 'value' => 'AL-KOBLAN Thermopipe Factory - The leading manufacturer of water pipes in Saudi Arabia since 1995. We are committed to the highest international quality standards.', 'group' => 'footer'],
            ['key' => 'footer_description_ar', 'value' => 'مصنع الكبلان للمواسير الحرارية - الشركة الرائدة في تصنيع مواسير المياه بالمملكة العربية السعودية منذ عام 1995. نلتزم بأعلى معايير الجودة الدولية.', 'group' => 'footer'],
            ['key' => 'copyright_text', 'value' => '© 2026 AL-KOBLAN Thermopipe Factory. All rights reserved.', 'group' => 'footer'],
            ['key' => 'copyright_text_ar', 'value' => '© 2026 مصنع الكبلان للمواسير الحرارية. جميع الحقوق محفوظة.', 'group' => 'footer'],

            // commerce
            ['key' => 'tax_rate', 'value' => '15', 'group' => 'commerce'],
            ['key' => 'free_shipping_threshold', 'value' => '0', 'group' => 'commerce'],
        ];

        foreach ($settings as $s) {
            Setting::query()->updateOrCreate(['key' => $s['key']], [
                'value' => $s['value'],
                'group' => $s['group'],
                'type' => $s['type'] ?? 'text',
            ]);
        }
    }
}
