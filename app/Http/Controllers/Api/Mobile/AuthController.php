<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Requests\Api\Mobile\LoginRequest;
use App\Http\Requests\Api\Mobile\RegisterRequest;
use App\Http\Resources\Api\Mobile\MobileUserResource;
use App\Models\Customer;
use App\Models\Usertokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends MobileController
{
    public function register(RegisterRequest $request)
    {
        $type = $request->validated('user_type');
        $ownerType = match ($type) {
            'owner', 'seller' => 'seller',
            'builder', 'developer' => 'builder',
            default => null,
        };

        try {
            [$customer, $token] = DB::transaction(function () use ($request, $ownerType) {
                $customer = Customer::create([
                    'name' => $request->validated('name'),
                    'email' => strtolower($request->validated('email')),
                    'password' => Hash::make($request->validated('password')),
                    'mobile' => $request->validated('mobile'),
                    'country_code' => $request->validated('country_code') ?: '+91',
                    'owner_type' => $ownerType,
                    'company_name' => $ownerType === 'builder' ? $request->validated('company_name') : null,
                    'city' => $request->validated('city'),
                    'state' => $request->validated('state'),
                    'auth_id' => (string) Str::uuid(),
                    'slug_id' => Str::slug($request->validated('name')).'-'.Str::lower(Str::random(5)),
                    'address' => '',
                    'logintype' => 'manual',
                    'is_email_verified' => 1,
                    'isActive' => 1,
                    'notification' => 1,
                    'kyc_status' => $ownerType ? 'pending' : null,
                ]);

                $token = $customer->createToken($request->validated('device_name') ?: 'bigwein-mobile')->plainTextToken;
                return [$customer, $token];
            });

            return $this->success([
                'token' => $token,
                'user' => (new MobileUserResource($customer->fresh()))->resolve($request),
            ], 'Account created successfully.', null, 201);
        } catch (Throwable $e) {
            report($e);
            return $this->error('Account could not be created. Please try again.', 500);
        }
    }

    public function login(LoginRequest $request)
    {
        $customer = Customer::whereRaw('LOWER(email) = ?', [strtolower($request->validated('email'))])->first();

        if (!$customer || !$customer->password || !Hash::check($request->validated('password'), $customer->password)) {
            return $this->error('Invalid email or password.', 401);
        }

        if (!(bool) $customer->isActive) {
            return $this->error('Your account has been deactivated.', 403);
        }
        if (!(bool) $customer->is_email_verified) {
            return $this->error('Please verify your email before logging in.', 403);
        }

        $token = $customer->createToken($request->validated('device_name') ?: 'bigwein-mobile')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => (new MobileUserResource($customer))->resolve($request),
        ], 'Login successful.');
    }

    public function me(Request $request)
    {
        return $this->success((new MobileUserResource($request->user()))->resolve($request));
    }

    public function logout(Request $request)
    {
        $validator = validator($request->all(), ['fcm_token' => ['nullable', 'string', 'max:4096']]);
        if ($validator->fails()) return $this->error($validator->errors()->first(), 422, $validator->errors()->toArray());

        if ($request->filled('fcm_token')) {
            Usertokens::where('customer_id', $request->user()->id)
                ->where('fcm_id', $request->string('fcm_token')->toString())
                ->delete();
        }

        $token = $request->user()->currentAccessToken();
        if ($token) $token->delete();

        return $this->success(null, 'Logged out successfully.');
    }
}
