<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ContactRequest;
use App\Http\Resources\ContactMessageResource;
use App\Services\ContactService;

class ContactController extends Controller
{
    public function __construct(protected ContactService $contactService)
    {
    }

    public function store(ContactRequest $request)
    {
        $message = $this->contactService->create($request->validated());

        return $this->successResponse(new ContactMessageResource($message), 'Your message has been sent successfully.', 201);
    }
}
