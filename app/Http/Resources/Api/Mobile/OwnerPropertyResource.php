<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerPropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'slug' => $this->slug_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category ? [
                'id' => (int) $this->category->id,
                'name' => $this->category->category,
            ] : null,
            'property_type' => (int) $this->getRawOriginal('propery_type'),
            'sub_type' => $this->sub_type,
            'commercial_type' => $this->commercial_type,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price' => $this->price,
            'total_area' => $this->total_area,
            'carpet_area' => $this->carpet_area,
            'floor_number' => $this->floor_number,
            'total_floors' => $this->total_floors,
            'age_of_building' => $this->age_of_building,
            'facing' => $this->facing,
            'furnishing' => $this->furnishing,
            'water_supply' => $this->water_supply,
            'property_status' => $this->prop_status,
            'maintenance' => $this->maintenance,
            'security_deposit' => $this->security_deposit,
            'price_negotiable' => (bool) $this->price_negotiable,
            'rent_duration' => $this->rentduration,
            'video_link' => $this->video_link,
            'title_image_url' => $this->title_image ?: null,
            'three_d_image' => $this->three_d_image ?: null,
            'gallery' => collect($this->gallery)->map(fn ($image) => [
                'id' => (int) $image->id,
                'image_url' => $image->image_url ?? null,
            ])->values()->all(),
            'amenity_ids' => collect($this->amenity_ids ?? [])->map(fn ($id) => (int) $id)->values()->all(),
            'parameters' => collect($this->mobile_parameters ?? [])->values()->all(),
            'facilities' => collect($this->mobile_facilities ?? [])->values()->all(),
            'approval_status' => $this->request_status,
            'is_active' => (bool) $this->status,
            'review_remarks' => $this->review_remarks,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
