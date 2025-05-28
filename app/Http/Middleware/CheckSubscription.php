<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $organization = $user->organization;
        $latestSubscription = $organization->subscriptions()->latest()->first();

        if (!$latestSubscription || now()->greaterThan($latestSubscription->subscription_end)) {
            return response()->json([
                'message' => 'Your subscription has expired. Please renew.',
                'subscription_status' => 'expired'
            ], 403);
        }

        return $next($request);
    }
}
