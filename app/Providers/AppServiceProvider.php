<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\SiteLayoutComposer;
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
    }
}
