<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $customer = session('bw_customer');
        if (!$customer) {
            return redirect('/owner/login')->with('error', 'Please login to continue.');
        }
        $ownerType = $customer['owner_type'] ?? null;
        if (empty($ownerType)) {
            return redirect('/owner/register')->with('error', 'Please complete your owner registration.');
        }
        return $next($request);
    }
}
