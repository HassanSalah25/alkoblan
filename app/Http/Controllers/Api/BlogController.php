<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BlogPostListResource;
use App\Http\Resources\BlogPostResource;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(protected BlogService $blogService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'category' => $request->query('category'),
            'tag' => $request->query('tag'),
            'q' => $request->query('q'),
            'per_page' => $request->query('per_page', 9),
            'page' => $request->query('page'),
        ];

        $posts = $this->blogService->paginate($filters);

        return $this->successResponse(
            BlogPostListResource::collection($posts),
            'Blog posts retrieved successfully.',
            200,
            $this->paginationMeta($posts)
        );
    }

    public function show(string $slug)
    {
        $post = $this->blogService->findBySlug($slug);

        if (! $post) {
            return $this->errorResponse('Blog post not found.', [], 404);
        }

        return $this->successResponse(new BlogPostResource($post), 'Blog post retrieved successfully.');
    }
}
