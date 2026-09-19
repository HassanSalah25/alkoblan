<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\MenuItemResource;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(protected MenuService $menuService)
    {
    }

    public function index(Request $request)
    {
        $location = $request->query('location', 'main');

        if (! in_array($location, MenuService::LOCATIONS, true)) {
            return $this->errorResponse('Invalid menu location.', ['location' => ['Must be one of: '.implode(', ', MenuService::LOCATIONS)]], 422);
        }

        $tree = $this->menuService->tree($location);

        return $this->successResponse(MenuItemResource::collection($tree), 'Menu retrieved successfully.');
    }
}
