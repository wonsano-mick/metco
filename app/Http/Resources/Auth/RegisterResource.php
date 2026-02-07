<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'success' => true,
            'message' => 'Registration successful. Please verify your email.',
            'data' => [
                'user_id' => $this->resource['user_id'],
                'requires_email_verification' => $this->resource['requires_email_verification'],
                'requires_phone_verification' => $this->resource['requires_phone_verification'],
            ],
            'meta' => [
                'request_id' => $request->header('X-Request-ID'),
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(201);
    }
}
