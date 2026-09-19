<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    public static function allCached(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever('settings.all', function () {
            return static::query()->get()->keyBy('key');
        });
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::allCached()->get($key);

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $group = 'general', string $type = 'text'): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group, 'type' => $type]);
    }
}
