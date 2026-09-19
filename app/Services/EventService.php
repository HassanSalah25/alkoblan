<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Event::published()->with('featuredImage');

        if (isset($filters['featured'])) {
            $query->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['upcoming']) && filter_var($filters['upcoming'], FILTER_VALIDATE_BOOLEAN)) {
            $query->upcoming();
        }

        $query->orderBy('event_date');

        $perPage = min(48, max(1, (int) ($filters['per_page'] ?? 12)));
        $page = isset($filters['page']) ? (int) $filters['page'] : null;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findBySlug(string $slug): ?Event
    {
        return Event::published()->where('slug', $slug)
            ->with(['featuredImage', 'images.media'])
            ->first();
    }
}
