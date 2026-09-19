<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;

abstract class Controller extends BaseController
{
    /**
     * Build a standard success envelope.
     */
    protected function successResponse($data = null, string $message = 'OK', int $code = 200, ?array $meta = null)
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $code);
    }

    /**
     * Build a standard error envelope.
     */
    protected function errorResponse(string $message = 'Error', array $errors = [], int $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            // cast so an empty array serializes as `{}` rather than `[]`, per the response envelope spec
            'errors' => empty($errors) ? (object) [] : $errors,
        ], $code);
    }

    /**
     * Fold Laravel's paginator metadata into our meta envelope shape.
     */
    protected function paginationMeta(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
