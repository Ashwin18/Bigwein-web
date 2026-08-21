<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Requests\Api\Mobile\KycSubmissionRequest;
use App\Http\Resources\Api\Mobile\MobileKycResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class KycController extends MobileController
{
    public function show(Request $request)
    {
        if (!in_array($request->user()->owner_type, ['seller', 'builder'], true)) {
            return $this->error('KYC is available only for Owner/Seller and Builder/Developer accounts.', 403);
        }

        $kyc = DB::table('customer_kyc')->where('customer_id', $request->user()->id)->first();

        return $this->success((new MobileKycResource($kyc))->resolve($request));
    }

    public function submit(KycSubmissionRequest $request)
    {
        $user = $request->user();
        if (!in_array($user->owner_type, ['seller', 'builder'], true)) {
            return $this->error('KYC is available only for Owner/Seller and Builder/Developer accounts.', 403);
        }

        $current = DB::table('customer_kyc')->where('customer_id', $user->id)->first();
        $currentStatus = strtolower((string) ($current?->status ?: $user->kyc_status ?: 'pending'));

        if (in_array($currentStatus, ['submitted', 'under_review'], true)) {
            return $this->error('Your KYC is under review and cannot be edited.', 422);
        }
        if ($currentStatus === 'approved') {
            return $this->error('Your KYC is already approved.', 422);
        }

        $folder = public_path('images/customer_kyc/'.$user->id);
        if (!File::isDirectory($folder)) File::makeDirectory($folder, 0755, true);

        $front = $current?->aadhaar_front;
        $back = $current?->aadhaar_back;
        $uploaded = [];

        try {
            if ($request->hasFile('aadhaar_front')) {
                $front = $this->storeDocument($request->file('aadhaar_front'), 'aadhaar_front', $folder);
                $uploaded[] = $folder.'/'.$front;
            }
            if ($request->hasFile('aadhaar_back')) {
                $back = $this->storeDocument($request->file('aadhaar_back'), 'aadhaar_back', $folder);
                $uploaded[] = $folder.'/'.$back;
            }

            DB::transaction(function () use ($request, $user, $current, $front, $back) {
                $data = [
                    'customer_id' => $user->id,
                    'aadhaar_number' => $request->validated('aadhaar_number'),
                    'aadhaar_front' => $front,
                    'aadhaar_back' => $back,
                    'status' => 'submitted',
                    'remarks' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ];

                if ($current) {
                    DB::table('customer_kyc')->where('id', $current->id)->update($data);
                } else {
                    $data['created_at'] = now();
                    DB::table('customer_kyc')->insert($data);
                }

                DB::table('customers')->where('id', $user->id)->update([
                    'aadhaar_number' => $request->validated('aadhaar_number'),
                    'kyc_status' => 'submitted',
                    'kyc_reject_reason' => null,
                    'kyc_verified_at' => null,
                    'kyc_verified_by' => null,
                    'updated_at' => now(),
                ]);
            });

            $kyc = DB::table('customer_kyc')->where('customer_id', $user->id)->first();
            return $this->success((new MobileKycResource($kyc))->resolve($request), 'KYC submitted successfully.');
        } catch (Throwable $e) {
            foreach ($uploaded as $path) if (is_file($path)) @unlink($path);
            report($e);
            return $this->error('KYC could not be submitted. Please try again.', 500);
        }
    }

    private function storeDocument($file, string $prefix, string $folder): string
    {
        $name = $prefix.'_'.time().'_'.Str::lower(Str::random(5)).'.'.strtolower($file->getClientOriginalExtension());
        $file->move($folder, $name);
        return $name;
    }
}
