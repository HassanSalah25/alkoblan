<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        return view('pages.events', [
            'events' => Event::published()->orderByDesc('event_date')->paginate(9),
        ]);
    }

    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)->published()->with('images.media')->firstOrFail();

        return view('pages.event-detail', [
            'event' => $event,
            'related' => Event::published()->where('id', '!=', $event->id)
                ->orderByDesc('event_date')->limit(3)->get(),
        ]);
    }
}
