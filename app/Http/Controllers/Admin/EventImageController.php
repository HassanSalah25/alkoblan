<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use Illuminate\Http\Request;

class EventImageController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'new_media_id' => ['required', 'exists:media,id'],
        ]);

        $sortOrder = ($event->images()->max('sort_order') ?? 0) + 1;

        $event->images()->create([
            'media_id' => $data['new_media_id'],
            'sort_order' => $sortOrder,
        ]);

        return redirect()->route('admin.events.edit', $event)->with('success', 'Image added to gallery.');
    }

    public function destroy(EventImage $event_image)
    {
        $event = $event_image->event;
        $event_image->delete();

        return redirect()->route('admin.events.edit', $event)->with('success', 'Image removed from gallery.');
    }
}
