<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\AddonsChecker;
use Illuminate\Support\Facades\Auth;

class CheckAddon
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $addonId
     * @return mixed
     */

    public function handle(Request $request, Closure $next, $addonId)
    {

        if (Auth::guard('global')->check()) {
            return $next($request);
        }

        if (!AddonsChecker::hasAddon((int) $addonId)) {
            return response()->view('errors.featurerequired', [], 403);
        }

        return $next($request);
    }
}
