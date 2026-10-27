<?php

/*
|--------------------------------------------------------------------------
| AL-KOBLAN AI Pipe Planner
|--------------------------------------------------------------------------
| Secrets (OPENROUTER_API_KEY) live only in the environment. Non-secret
| operational limits can be overridden from the admin panel; those
| overrides are stored in the settings table (group "pipe_planner") and
| resolved through App\Services\PipePlanner\PlannerSettings.
*/

return [

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        // Must be a vision-capable model id from https://openrouter.ai/models
        // (input_modalities includes "image"). Verified at runtime.
        'model' => env('OPENROUTER_MODEL'),
        'timeout' => (int) env('OPENROUTER_TIMEOUT', 120),
        'retries' => (int) env('OPENROUTER_RETRIES', 2),
        'max_tokens' => (int) env('OPENROUTER_MAX_TOKENS', 6000),
        'app_title' => env('OPENROUTER_APP_TITLE', 'AL-KOBLAN AI Pipe Planner'),
        'retry_backoff_ms' => (int) env('OPENROUTER_RETRY_BACKOFF_MS', 1500),
    ],

    'limits' => [
        'max_images' => (int) env('PIPE_PLANNER_MAX_IMAGES', 8),
        'max_image_kb' => (int) env('PIPE_PLANNER_MAX_IMAGE_KB', 8192),
        'analyses_per_day' => (int) env('PIPE_PLANNER_ANALYSES_PER_DAY', 10),
        // Monthly ceiling on provider cost (USD) reported by OpenRouter; 0 = no ceiling.
        'monthly_budget_usd' => (float) env('PIPE_PLANNER_MONTHLY_BUDGET_USD', 50),
        'max_catalog_products' => (int) env('PIPE_PLANNER_MAX_CATALOG_PRODUCTS', 120),
    ],

    'retention_days' => (int) env('PIPE_PLANNER_RETENTION_DAYS', 90),

    'show_prices' => (bool) env('PIPE_PLANNER_SHOW_PRICES', true),

    // Private disk for customer photos (never the public disk).
    'disk' => env('PIPE_PLANNER_DISK', 'local'),

    'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],

    // Category slugs from the existing catalog, mapped to planner component categories.
    'category_map' => [
        'pipe' => ['pipes', 'ktp-thermopipes', 'ktp-multilayer-pipes'],
        'elbow' => ['elbows'],
        'tee' => ['tee'],
        'coupling' => ['coupling'],
        'reducer' => ['coupling'],
        'adapter' => ['adapter'],
        'union' => ['union-joint'],
        'valve' => ['valves', 'ball-valves'],
        'end_cap' => ['fittings-others'],
        'other' => ['fittings-others'],
    ],
];
