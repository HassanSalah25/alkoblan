<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Str;

trait GeneratesSlugs
{
    /**
     * Generate a unique slug for the given model class/table, based on $source
     * (or explicit $desired), ignoring the row with id $ignoreId (for updates).
     */
    protected function uniqueSlug(string $modelClass, string $source, ?int $ignoreId = null, ?string $desired = null): string
    {
        $base = Str::slug($desired ?: $source);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $i = 1;

        while (
            $modelClass::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
