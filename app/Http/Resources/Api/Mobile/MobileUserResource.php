<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MobileUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $ownerType = $this->owner_type;
        $kycStatus = strtolower((string) ($this->kyc_status ?: 'pending'));
        $builderStatus = $ownerType === 'builder'
            ? strtolower((string) (DB::table('builder_profiles')->where('customer_id', $this->id)->value('status') ?: 'not_submitted'))
            : null;
        $kycRemarks = DB::table('customer_kyc')->where('customer_id', $this->id)->value('remarks');

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->getRawOriginal('mobile'),
            'country_code' => $this->country_code,
            'profile_url' => $this->profile ?: null,
            'user_type' => $ownerType === 'builder' ? 'builder' : ($ownerType === 'seller' ? 'owner' : 'customer'),
            'owner_type' => $ownerType,
            'kyc_status' => $kycStatus,
            'kyc_remarks' => in_array($kycStatus, ['rejected', 'changes_requested'], true)
                ? ($kycRemarks ?: $this->kyc_reject_reason)
                : null,
            'builder_verification_status' => $builderStatus,
            'can_post_property' => in_array($ownerType, ['seller', 'builder'], true) && $kycStatus === 'approved',
            'can_post_project' => $ownerType === 'builder' && $kycStatus === 'approved' && $builderStatus === 'approved',
            'notification_enabled' => (bool) $this->notification,
        ];
    }
}
