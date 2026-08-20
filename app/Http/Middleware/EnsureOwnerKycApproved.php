<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureOwnerKycApproved
{
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session()->get('bw_customer', []);
        $id = $session['id'] ?? $session['customer_id'] ?? null;

        if (!$id && !empty($session['email'])) {
            $id = DB::table('customers')->where('email', $session['email'])->value('id');
        }
        if (!$id && !empty($session['mobile'])) {
            $id = DB::table('customers')->where('mobile', $session['mobile'])->value('id');
        }

        if (!$id) return redirect('/owner/login')->with('error', 'Please login to continue.');

        $owner = DB::table('customers')->where('id', $id)->whereNotNull('owner_type')->first();
        if (!$owner) return redirect('/owner/login')->with('error', 'Owner account not found.');

        if (strtolower((string)($owner->kyc_status ?? 'pending')) !== 'approved') {
            return redirect('/owner/kyc')->with('error', 'KYC approval is required before you can post a property or project.');
        }

        return $next($request);
    }
}
