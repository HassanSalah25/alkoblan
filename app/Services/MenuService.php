<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

class MenuService
{
    public const LOCATIONS = ['main', 'mobile', 'footer_quick', 'footer_products', 'footer_bottom'];

    /**
     * Nested parent/children tree for a given menu location, active only,
     * ordered by sort_order.
     */
    public function tree(string $location): Collection
    {
        return MenuItem::query()
            ->location($location)
            ->active()
            ->topLevel()
            ->orderBy('sort_order')
            ->with('children')
            ->get();
    }
}
