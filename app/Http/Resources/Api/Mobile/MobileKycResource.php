<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileKycResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $record = $this->resource;
        $status = strtolower((string) ($record?->status ?: $request->user()?->kyc_status ?: 'pending'));
        $aadhaar = preg_replace('/\D+/', '', (string) ($record?->aadhaar_number ?? ''));

        return [
            'status' => $status,
            'aadhaar_masked' => strlen($aadhaar) === 12 ? 'XXXX XXXX '.substr($aadhaar, -4) : null,
            'aadhaar_front_url' => $this->documentUrl($request, $record?->aadhaar_front),
            'aadhaar_back_url' => $this->documentUrl($request, $record?->aadhaar_back),
            'submitted_at' => $record?->submitted_at,
            'reviewed_at' => $record?->approved_at,
            'remarks' => in_array($status, ['rejected', 'changes_requested'], true) ? $record?->remarks : null,
            'can_resubmit' => in_array($status, ['pending', 'not_submitted', 'rejected', 'changes_requested'], true),
            'can_post_property' => in_array($request->user()?->owner_type, ['seller', 'builder'], true) && $status === 'approved',
        ];
    }

    private function documentUrl(Request $request, ?string $filename): ?string
    {
        if (!$filename || !$request->user()) return null;

        return asset('images/customer_kyc/'.$request->user()->id.'/'.rawurlencode(basename($filename)));
    }
}
