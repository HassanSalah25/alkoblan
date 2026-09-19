<?php

namespace App\Http\Controllers\Api;

use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Public-safe settings as a flat key => value object. Nothing in this
     * project's settings table is secret, so every group is exposed here.
     */
    public function index()
    {
        $settings = Setting::allCached()->map(fn (Setting $setting) => $setting->value);

        return $this->successResponse($settings, 'Settings retrieved successfully.');
    }
}
