<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PageResource;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->with('featuredImage')->first();

        if (! $page) {
            return $this->errorResponse('Page not found.', [], 404);
        }

        return $this->successResponse(new PageResource($page), 'Page retrieved successfully.');
    }
}
