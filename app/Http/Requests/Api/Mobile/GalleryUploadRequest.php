<?php

namespace App\Http\Requests\Api\Mobile;

class GalleryUploadRequest extends MobileFormRequest
{
    public function rules(): array
    {
        return [
            'gallery' => ['required', 'array', 'min:1', 'max:20'],
            'gallery.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
