<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionHelper;
use App\Models\BranchSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionsController extends Controller
{
    //

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function paymentIndex(Request $request)
    {
        // Get the authenticated user
        $authUser = $this->authUser();

        // Subscription
        return view('tenant.payments.payment', ['authUser' => $authUser]);
    }
    
    public function subscriptionStatus(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(57);

        // Get the user's branch_id from employment details
        $employmentDetail = $authUser->employmentDetail;
        if (!$employmentDetail || !$employmentDetail->branch_id) {
            return response()->json(['message' => 'Branch not found for user.'], 404);
        }
        $branchId = $employmentDetail->branch_id;

        // Get branch subscriptions
        $subscriptions = BranchSubscription::with(['payments'])
            ->where('branch_id', $branchId)
            ->orderByDesc('subscription_start')
            ->get();

        // Calculate trial days left
        $trialDaysLeft = null;
        $trialEndDate = null;
        $latestSubscription = $subscriptions->first();
        if ($latestSubscription && $latestSubscription->trial_end) {
            $now = now();
            $trialEnd = \Carbon\Carbon::parse($latestSubscription->trial_end);
            $trialEndDate = $trialEnd->toDateString();
            $trialDaysLeft = $now->lt($trialEnd) ? (int) round($now->diffInDays($trialEnd)) : 0;
        }

        // Calculate subscription days left
        $subscriptionDaysLeft = null;
        if ($latestSubscription && $latestSubscription->subscription_end) {
            $now = now();
            $subscriptionEnd = \Carbon\Carbon::parse($latestSubscription->subscription_end);
            $subscriptionDaysLeft = $now->lt($subscriptionEnd) ? (int) round($now->diffInDays($subscriptionEnd)) : 0;
        }

        return response()->json([
            'branch_id' => $branchId,
            'trial_days_left' => $trialDaysLeft,
            'trial_end_date' => $trialEndDate,
            'subscription_days_left' => $subscriptionDaysLeft,
            'permission' => $permission,
        ]);
    }
}
