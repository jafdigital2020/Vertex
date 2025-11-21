<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\BranchSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Global admins bypass subscription checks
        if (Auth::guard('global')->check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get user's branch subscription
        $employmentDetail = $user->employmentDetail;

        if (!$employmentDetail || !$employmentDetail->branch_id) {
            // If no employment detail or branch, allow access (shouldn't happen in normal flow)
            return $next($request);
        }

        $branchId = $employmentDetail->branch_id;

        // Get the latest active subscription for this branch
        $subscription = BranchSubscription::where('branch_id', $branchId)
            ->latest()
            ->first();

        if (!$subscription) {
            Log::info('CheckSubscription: No subscription found', [
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'route' => $request->path()
            ]);

            return $this->handleExpiredSubscription($request, 'No active subscription found. Please contact your administrator.');
        }

        $now = Carbon::now();
        $subscriptionEnd = Carbon::parse($subscription->subscription_end);
        $daysLeft = $now->diffInDays($subscriptionEnd, false);

        // Check if subscription has expired (0 days left or negative)
        if ($daysLeft <= 0) {
            Log::info('CheckSubscription: Subscription expired', [
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'subscription_end' => $subscription->subscription_end,
                'days_left' => $daysLeft,
                'route' => $request->path()
            ]);

            $message = $subscription->is_trial
                ? 'Your trial period has ended. Please contact your administrator to activate your subscription.'
                : 'Your subscription has expired. Please contact your administrator to renew.';

            return $this->handleExpiredSubscription($request, $message);
        }

        // Check if subscription is explicitly expired/cancelled/inactive
        if (in_array($subscription->status, ['expired', 'cancelled', 'inactive'])) {
            Log::info('CheckSubscription: Subscription status inactive', [
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'subscription_status' => $subscription->status,
                'route' => $request->path()
            ]);

            return $this->handleExpiredSubscription($request, 'Your subscription is no longer active. Please contact your administrator.');
        }

        return $next($request);
    }

    /**
     * Handle expired subscription response
     *
     * @param Request $request
     * @param string $message
     * @return Response
     */
    private function handleExpiredSubscription(Request $request, string $message): Response
    {
        // For AJAX/API requests, return JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'subscription_expired' => true
            ], 403);
        }

        // For web requests, store in session and show modal
        session()->flash('subscription_expired', $message);

        // Redirect to employee dashboard where modal will be shown
        return redirect()->route('employee-dashboard');
    }
}
