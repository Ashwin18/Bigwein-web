<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Validation\Rule;

class RegisterRequest extends MobileFormRequest
{
    protected function prepareForValidation(): void
    {
        $type = strtolower(trim((string) $this->input('user_type', 'customer')));
        $this->merge(['user_type' => $type]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190', Rule::unique('customers', 'email')],
            'mobile' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'mobile')],
            'country_code' => ['nullable', 'string', 'max:10'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'user_type' => ['required', Rule::in(['customer', 'owner', 'seller', 'builder', 'developer'])],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
