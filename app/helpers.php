<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('media_url')) {
    function media_url($media): ?string
    {
        return $media?->url;
    }
}

if (! function_exists('trans_field')) {
    /**
     * Return the locale-aware value of a translatable field pair (field / field_ar).
     */
    function trans_field($model, string $field)
    {
        if (! $model) {
            return null;
        }
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            return $model->{$field.'_ar'} ?: $model->{$field};
        }

        return $model->{$field};
    }
}

if (! function_exists('google_maps_embed_url')) {
    /**
     * Resolve a Google Maps iframe src. Invalid or truncated embed?pb= URLs fall back to coordinates or address.
     */
    function google_maps_embed_url(?string $configured = null, ?float $latitude = null, ?float $longitude = null, ?string $addressQuery = null): string
    {
        $configured = trim((string) ($configured ?? setting('google_maps_embed', '')));

        if (preg_match('/src=["\']([^"\']+)["\']/i', $configured, $matches)) {
            $configured = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        }

        if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_URL)) {
            if (str_contains($configured, 'output=embed')) {
                return $configured;
            }

            if (preg_match('/embed(?:\/v1\/[^?]+)?\?pb=([^&]+)/', $configured, $matches)) {
                $pb = $matches[1];
                $pbLooksValid = strlen($pb) > 100
                    && (str_contains($pb, '!4v') || str_contains($pb, '!5m2') || str_contains($pb, '%3A'));

                if ($pbLooksValid) {
                    return $configured;
                }
            } elseif (str_contains($configured, '/maps/embed') && ! str_contains($configured, '?pb=')) {
                return $configured;
            } elseif (preg_match('/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $configured, $matches)) {
                $latitude = (float) $matches[1];
                $longitude = (float) $matches[2];
            } elseif (preg_match('/[?&]q=([^&]+)/', $configured, $matches)) {
                $addressQuery = urldecode($matches[1]);
            }
        }

        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        if ($latitude !== null && $longitude !== null) {
            return sprintf(
                'https://maps.google.com/maps?q=%s,%s&hl=%s&z=14&output=embed',
                $latitude,
                $longitude,
                $locale
            );
        }

        $addressQuery = $addressQuery ?: setting(
            app()->getLocale() === 'ar' ? 'contact_address_ar' : 'contact_address',
            'Riyadh, Saudi Arabia'
        );

        return 'https://maps.google.com/maps?q='.rawurlencode($addressQuery)."&hl={$locale}&z=12&output=embed";
    }
}
