<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Validation\Rule;

class KycSubmissionRequest extends MobileFormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->owner_type, ['seller', 'builder'], true);
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'error' => true,
            'message' => 'KYC is available only for Owner/Seller and Builder/Developer accounts.',
            'errors' => (object) [],
        ], 403));
    }

    public function rules(): array
    {
        $current = $this->user()
            ? \DB::table('customer_kyc')->where('customer_id', $this->user()->id)->first()
            : null;

        return [
            'aadhaar_number' => [
                'required',
                'regex:/^[0-9]{12}$/',
                Rule::unique('customer_kyc', 'aadhaar_number')->ignore($current?->id),
            ],
            'aadhaar_front' => [
                $current && !empty($current->aadhaar_front) ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'aadhaar_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'aadhaar_number.regex' => 'Aadhaar number must contain exactly 12 digits.',
            'aadhaar_front.required' => 'Please upload the front side of the Aadhaar card.',
            'aadhaar_number.unique' => 'This Aadhaar number is already linked with another account.',
        ];
    }
}
