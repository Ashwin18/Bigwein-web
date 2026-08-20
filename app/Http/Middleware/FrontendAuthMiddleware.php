<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
/**
 * Middleware: bw.auth
 * Add to app/Http/Kernel.php -> $routeMiddleware:
 *   'bw.auth' => \App\Http\Middleware\FrontendAuthMiddleware::class,
 */
class FrontendAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('bw_customer')) {
            return $next($request);
        }
        return response()->json(['error' => true, 'message' => 'Please login to continue.'], 401);
    }
}
