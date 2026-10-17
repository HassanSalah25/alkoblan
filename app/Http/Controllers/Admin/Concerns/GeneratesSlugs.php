<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Str;

trait GeneratesSlugs
{
    /**
     * Generate a unique slug for the given model class/table, based on $source
     * (or explicit $desired), ignoring the row with id $ignoreId (for updates).
     */
    protected function uniqueSlug(
        string $modelClass,
        string $source,
        ?int $ignoreId = null,
        ?string $desired = null,
        string $column = 'slug',
        bool $unicode = false,
    ): string {
        $raw = trim((string) (($desired !== null && $desired !== '') ? $desired : $source));
        // language: null keeps Arabic letters. The default transliterates them to Franco Latin.
        $base = $unicode ? Str::slug($raw, '-', null) : Str::slug($raw);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $i = 1;

        while (
            $modelClass::where($column, $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
