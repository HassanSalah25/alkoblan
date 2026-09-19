<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BranchResource;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::active()->get();

        return $this->successResponse(BranchResource::collection($branches), 'Branches retrieved successfully.');
    }
}
