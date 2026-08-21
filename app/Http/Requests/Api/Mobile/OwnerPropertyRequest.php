<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class OwnerPropertyRequest extends MobileFormRequest
{
    abstract protected function titleImageRule(): string;

    private function categoryProfile(): string
    {
        $name = strtolower((string) \DB::table('categories')
            ->where('id', $this->input('category_id'))
            ->value('category'));

        if (str_contains($name, 'commercial') || str_contains($name, 'office') || str_contains($name, 'shop') || str_contains($name, 'showroom') || str_contains($name, 'warehouse') || str_contains($name, 'industrial') || str_contains($name, 'factory')) return 'commercial';
        if (str_contains($name, 'plot') || str_contains($name, 'land') || str_contains($name, 'agricultural')) return 'plot';
        if (str_contains($name, 'pg') || str_contains($name, 'hostel') || str_contains($name, 'co-living')) return 'pg';
        if (str_contains($name, 'villa') || str_contains($name, 'town') || str_contains($name, 'apartment') || str_contains($name, 'flat') || str_contains($name, 'house') || str_contains($name, 'bungalow') || str_contains($name, 'banglow') || str_contains($name, 'penthouse') || str_contains($name, 'condo')) return 'residential';

        return 'generic';
    }

    public function rules(): array
    {
        $profile = $this->categoryProfile();

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('status', 1))],
            'property_type' => ['required', Rule::in([0, 1, '0', '1'])],
            'sub_type' => [$profile === 'commercial' ? 'required' : 'nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'price' => ['required', 'numeric', 'min:1'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'carpet_area' => [$profile === 'plot' ? 'nullable' : 'nullable', 'numeric', 'min:0'],
            'floor_number' => [$profile === 'plot' ? 'nullable' : 'nullable', 'integer', 'min:0'],
            'total_floors' => [$profile === 'plot' ? 'nullable' : 'nullable', 'integer', 'min:0'],
            'age_of_building' => ['nullable', 'string', 'max:100'],
            'facing' => ['nullable', 'string', 'max:100'],
            'furnishing' => ['nullable', 'string', 'max:100'],
            'water_supply' => ['nullable', 'string', 'max:100'],
            'property_status' => ['nullable', 'string', 'max:100'],
            'maintenance' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'price_negotiable' => ['nullable', 'boolean'],
            'rent_duration' => ['nullable', 'string', 'max:100'],
            'video_link' => ['nullable', 'url', 'max:1000'],
            'title_image' => [$this->titleImageRule(), 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'three_d_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'amenity_ids' => ['sometimes', 'array'],
            'amenity_ids.*' => ['integer', Rule::exists('property_amenities', 'id')->where(fn ($q) => $q->where('is_active', 1))],
            'parameters' => ['nullable', 'array'],
            'parameters.*.parameter_id' => ['required', 'integer', 'exists:parameters,id'],
            'parameters.*.value' => ['nullable'],
            'facilities' => ['nullable', 'array'],
            'facilities.*.facility_id' => ['required', 'integer', 'exists:outdoor_facilities,id'],
            'facilities.*.distance' => ['required', 'numeric', 'min:0'],
            'land_type' => ['nullable', 'string', 'max:100'],
            'area_unit' => ['nullable', 'string', 'max:30'],
            'road_width' => ['nullable', 'string', 'max:100'],
            'boundary_wall' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->categoryProfile() !== 'residential') return;

            $parameterNames = \DB::table('parameters')->pluck('name', 'id');
            foreach ($this->input('parameters', []) as $index => $parameter) {
                $name = preg_replace('/[^a-z0-9]+/', '', strtolower((string) $parameterNames->get((int) ($parameter['parameter_id'] ?? 0))));
                if (!str_contains($name, 'bedroom') && !str_contains($name, 'bathroom') && !str_contains($name, 'bhk')) continue;
                $value = $parameter['value'] ?? null;
                if ($value !== null && $value !== '' && (!is_numeric($value) || (int) $value < 0 || (int) $value > 100)) {
                    $validator->errors()->add("parameters.$index.value", 'Bedroom, bathroom and BHK values must be numbers between 0 and 100.');
                }
            }
        });
    }
}
