<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EventListResource;
use App\Http\Resources\EventResource;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(protected EventService $eventService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'featured' => $request->query('featured'),
            'upcoming' => $request->query('upcoming'),
            'per_page' => $request->query('per_page', 12),
            'page' => $request->query('page'),
        ];

        $events = $this->eventService->paginate($filters);

        return $this->successResponse(
            EventListResource::collection($events),
            'Events retrieved successfully.',
            200,
            $this->paginationMeta($events)
        );
    }

    public function show(string $slug)
    {
        $event = $this->eventService->findBySlug($slug);

        if (! $event) {
            return $this->errorResponse('Event not found.', [], 404);
        }

        return $this->successResponse(new EventResource($event), 'Event retrieved successfully.');
    }
}
