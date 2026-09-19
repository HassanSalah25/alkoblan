<?php

namespace App\View\Composers;

use App\Models\MenuItem;
use Illuminate\View\View;

class SiteLayoutComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'mainMenu' => MenuItem::location('main')->topLevel()->active()
                ->with('children')->orderBy('sort_order')->get(),
            'footerQuickLinks' => MenuItem::location('footer_quick')->active()->orderBy('sort_order')->get(),
            'footerProductLinks' => MenuItem::location('footer_products')->active()->orderBy('sort_order')->get(),
            'footerBottomLinks' => MenuItem::location('footer_bottom')->active()->orderBy('sort_order')->get(),
            'cartCount' => \App\Services\WebCart::count(),
        ]);
    }
}
