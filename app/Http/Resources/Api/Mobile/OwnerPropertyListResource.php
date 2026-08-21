<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerPropertyListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'slug' => $this->slug_id,
            'title' => $this->title,
            'title_image_url' => $this->title_image ?: null,
            'category' => $this->category ? [
                'id' => (int) $this->category->id,
                'name' => $this->category->category,
            ] : null,
            'city' => $this->city,
            'locality' => $this->address,
            'price' => $this->price,
            'property_type' => $this->getRawOriginal('propery_type') !== null
                ? (int) $this->getRawOriginal('propery_type')
                : null,
            'approval_status' => $this->request_status,
            'is_active' => (bool) $this->status,
            'review_remarks' => in_array($this->request_status, ['rejected', 'changes_requested'], true)
                ? $this->review_remarks
                : null,
            'gallery_count' => (int) ($this->gallery_count ?? 0),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
