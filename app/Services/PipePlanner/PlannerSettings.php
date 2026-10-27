<?php

namespace App\Services\PipePlanner;

use App\Models\Setting;

/**
 * Resolves Pipe Planner operational settings: admin overrides (settings table,
 * group "pipe_planner") take precedence over config/pipe_planner.php.
 * The API key is deliberately NOT overridable here — it stays env-only.
 */
class PlannerSettings
{
    public const KEYS = [
        'model' => ['config' => 'pipe_planner.openrouter.model', 'type' => 'string'],
        'max_images' => ['config' => 'pipe_planner.limits.max_images', 'type' => 'int'],
        'max_image_kb' => ['config' => 'pipe_planner.limits.max_image_kb', 'type' => 'int'],
        'analyses_per_day' => ['config' => 'pipe_planner.limits.analyses_per_day', 'type' => 'int'],
        'monthly_budget_usd' => ['config' => 'pipe_planner.limits.monthly_budget_usd', 'type' => 'float'],
        'max_tokens' => ['config' => 'pipe_planner.openrouter.max_tokens', 'type' => 'int'],
        'retention_days' => ['config' => 'pipe_planner.retention_days', 'type' => 'int'],
        'show_prices' => ['config' => 'pipe_planner.show_prices', 'type' => 'bool'],
    ];

    public function get(string $key)
    {
        $def = self::KEYS[$key];
        $override = Setting::get('pipe_planner_'.$key);
        $value = ($override === null || $override === '') ? config($def['config']) : $override;

        return match ($def['type']) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value === null ? null : (string) $value,
        };
    }

    public function set(string $key, $value): void
    {
        Setting::set('pipe_planner_'.$key, $value === null ? '' : (string) $value, 'pipe_planner');
    }

    public function all(): array
    {
        return collect(self::KEYS)->keys()->mapWithKeys(fn ($k) => [$k => $this->get($k)])->all();
    }

    public function apiKeyConfigured(): bool
    {
        return filled(config('pipe_planner.openrouter.api_key'));
    }
}
