<?php

namespace App\Http\Requests\Api\Mobile;

class LoginRequest extends MobileFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
