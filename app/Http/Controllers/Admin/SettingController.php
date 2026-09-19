<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** General tab bundles general + contact + footer + commerce groups (see spec). */
    private const GENERAL_KEYS = [
        'site_name', 'site_name_ar', 'logo', 'favicon', 'default_language', 'default_currency', 'founded_year',
        'contact_email', 'contact_phone', 'contact_phone_secondary', 'contact_whatsapp',
        'contact_address', 'contact_address_ar', 'working_hours', 'working_hours_ar', 'google_maps_embed',
        'footer_description', 'footer_description_ar', 'copyright_text', 'copyright_text_ar',
        'tax_rate', 'free_shipping_threshold',
    ];

    private const SEO_KEYS = [
        'seo_default_title', 'seo_default_title_ar', 'seo_default_description', 'seo_default_description_ar',
        'seo_default_keywords', 'google_analytics_id', 'google_tag_manager_id',
    ];

    private const SOCIAL_KEYS = [
        'social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin', 'social_youtube',
    ];

    public function general()
    {
        return view('admin.settings.general', ['values' => $this->valuesFor(self::GENERAL_KEYS)]);
    }

    public function updateGeneral(Request $request)
    {
        $this->persist($request, self::GENERAL_KEYS, [
            'general' => ['site_name', 'site_name_ar', 'logo', 'favicon', 'default_language', 'default_currency', 'founded_year'],
            'contact' => ['contact_email', 'contact_phone', 'contact_phone_secondary', 'contact_whatsapp', 'contact_address', 'contact_address_ar', 'working_hours', 'working_hours_ar', 'google_maps_embed'],
            'footer' => ['footer_description', 'footer_description_ar', 'copyright_text', 'copyright_text_ar'],
            'commerce' => ['tax_rate', 'free_shipping_threshold'],
        ]);

        return redirect()->route('admin.settings.general')->with('success', 'General settings saved.');
    }

    public function seo()
    {
        return view('admin.settings.seo', ['values' => $this->valuesFor(self::SEO_KEYS)]);
    }

    public function updateSeo(Request $request)
    {
        $this->persist($request, self::SEO_KEYS, ['seo' => self::SEO_KEYS]);

        return redirect()->route('admin.settings.seo')->with('success', 'SEO settings saved.');
    }

    public function social()
    {
        return view('admin.settings.social', ['values' => $this->valuesFor(self::SOCIAL_KEYS)]);
    }

    public function updateSocial(Request $request)
    {
        $this->persist($request, self::SOCIAL_KEYS, ['social' => self::SOCIAL_KEYS]);

        return redirect()->route('admin.settings.social')->with('success', 'Social media settings saved.');
    }

    private function valuesFor(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = Setting::get($key, '');
        }

        return $values;
    }

    /** Persist each posted key into its Setting row, grouped per $groupMap = ['group' => [keys...]]. */
    private function persist(Request $request, array $keys, array $groupMap): void
    {
        foreach ($groupMap as $group => $groupKeys) {
            foreach ($groupKeys as $key) {
                if ($request->has($key)) {
                    Setting::set($key, $request->input($key), $group);
                }
            }
        }
    }
}
