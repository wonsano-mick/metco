<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'success' => true,
            'data' => [
                'access_token' => $this->resource['tokens']['access_token'],
                'refresh_token' => $this->resource['tokens']['refresh_token'],
                'expires_in' => $this->resource['tokens']['expires_in'],
                'token_type' => $this->resource['tokens']['token_type'],
                'user' => $this->resource['user'],
                'requires_2fa' => $this->resource['requires_2fa'],
            ],
            'meta' => [
                'request_id' => $request->header('X-Request-ID'),
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
