<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:100'],
            'device_info' => ['nullable', 'array'],
            'device_info.os' => ['nullable', 'string'],
            'device_info.browser' => ['nullable', 'string'],
            'device_info.device_type' => ['nullable', 'string'],
        ];
    }
}
