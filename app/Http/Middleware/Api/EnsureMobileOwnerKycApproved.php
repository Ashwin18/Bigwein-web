<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileOwnerKycApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthenticated.',
                'errors' => (object) [],
            ], 401);
        }

        if (!in_array($user->owner_type, ['seller', 'builder'], true)) {
            return response()->json([
                'error' => true,
                'message' => 'An Owner/Seller account is required for property listing operations.',
                'errors' => (object) [],
            ], 403);
        }

        if (strtolower((string) ($user->kyc_status ?: 'pending')) !== 'approved') {
            return response()->json([
                'error' => true,
                'message' => 'Approved KYC is required before performing property listing operations.',
                'errors' => (object) [],
            ], 403);
        }

        return $next($request);
    }
}
