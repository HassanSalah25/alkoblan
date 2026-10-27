<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\SiteLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.app', 'partials.header', 'partials.footer', 'partials.mobile-menu'], SiteLayoutComposer::class);
        View::composer('admin.layouts.app', AdminLayoutComposer::class);

        // AI Pipe Planner: named limiters so each has its own counter.
        $by = fn (Request $request, string $name) => $name.':'.($request->user()?->id ?: $request->ip());
        RateLimiter::for('pipe-planner', fn (Request $r) => Limit::perMinute(120)->by($by($r, 'pp')));
        RateLimiter::for('pipe-planner-create', fn (Request $r) => Limit::perMinute(20)->by($by($r, 'pp-create')));
        RateLimiter::for('pipe-planner-upload', fn (Request $r) => Limit::perMinute(40)->by($by($r, 'pp-upload')));
        RateLimiter::for('pipe-planner-analysis', fn (Request $r) => Limit::perMinute(6)->by($by($r, 'pp-analysis')));
        RateLimiter::for('pipe-planner-quote', fn (Request $r) => Limit::perMinute(5)->by($by($r, 'pp-quote')));
    }
}
