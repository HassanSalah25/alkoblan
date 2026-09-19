<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\JobApplicationRequest;
use App\Http\Resources\JobApplicationResource;
use App\Http\Resources\JobOpeningResource;
use App\Services\CareerService;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function __construct(protected CareerService $careerService)
    {
    }

    public function index(Request $request)
    {
        $perPage = min(48, max(1, (int) $request->query('per_page', 12)));
        $page = $request->query('page');

        $jobs = $this->careerService->paginate($perPage, $page ? (int) $page : null);

        return $this->successResponse(
            JobOpeningResource::collection($jobs),
            'Job openings retrieved successfully.',
            200,
            $this->paginationMeta($jobs)
        );
    }

    public function show(string $slug)
    {
        $job = $this->careerService->findBySlug($slug);

        if (! $job) {
            return $this->errorResponse('Job opening not found.', [], 404);
        }

        return $this->successResponse(new JobOpeningResource($job), 'Job opening retrieved successfully.');
    }

    public function apply(JobApplicationRequest $request, string $slug)
    {
        $job = $this->careerService->findBySlug($slug);

        if (! $job) {
            return $this->errorResponse('Job opening not found.', [], 404);
        }

        $application = $this->careerService->apply($job, $request->validated(), $request->file('cv'));

        return $this->successResponse(new JobApplicationResource($application), 'Your application has been submitted successfully.', 201);
    }
}
