<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    /**
     * Ensure the user has an active subscription (or is on trial).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->subscribed('default')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('An active subscription is required.'),
                ], 403);
            }

            return redirect()->route('billing.index')
                ->with('warning', __('Please subscribe to access this feature.'));
        }

        return $next($request);
    }
}
