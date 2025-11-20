<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\AddonsChecker;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckAddon
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|string  $addonIdentifier - Can be addon ID or addon_key
     * @return mixed
     */

    public function handle(Request $request, Closure $next, $addonIdentifier)
    {
        // Global admins bypass addon checks
        if (Auth::guard('global')->check()) {
            return $next($request);
        }

        // Check if identifier is numeric (ID) or string (addon_key)
        if (is_numeric($addonIdentifier)) {
            $addonId = (int) $addonIdentifier;

            // Verify addon exists in database
            $addon = \App\Models\Addon::find($addonId);
            if (!$addon) {
                Log::info('CheckAddon: Access denied - Addon not exist', [
                    'addon_id' => $addonId,
                    'route' => $request->path()
                ]);
                return response()->view('errors.featurerequired', [], 403);
            }
        } else {
            // Look up addon by addon_key
            $addon = \App\Models\Addon::where('addon_key', $addonIdentifier)->first();

            // Addon not found in database
            if (!$addon) {
                Log::info('CheckAddon: Access denied - Addon not exist', [
                    'addon_key' => $addonIdentifier,
                    'route' => $request->path()
                ]);
                return response()->view('errors.featurerequired', [], 403);
            }

            $addonId = $addon->id;
        }

        // Check if addon is active in branch_addons (works for both 'addon' and 'upgrade' categories)
        $hasAccess = AddonsChecker::hasAddon($addonId);

        if (!$hasAccess) {
            Log::info('CheckAddon: Access denied - Addon not active', [
                'addon_id' => $addonId,
                'addon_identifier' => $addonIdentifier,
                'addon_category' => $addon->addon_category,
                'user_id' => Auth::id(),
                'route' => $request->path()
            ]);

            // Determine the error view based on addon category
            $errorView = $addon->addon_category === 'addon' ? 'errors.addonrequired' : 'errors.featurerequired';

            // For AJAX/API requests, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Access denied. This feature requires an active addon subscription.',
                    'addon_required' => true,
                    'addon_category' => $addon->addon_category
                ], 403);
            }

            // Store in session for modal display - let JavaScript show the modal
            session()->flash('addon_redirect', $errorView);

            // Continue to the page and let the modal show via JavaScript in footer-scripts
        }

        return $next($request);
    }
}
