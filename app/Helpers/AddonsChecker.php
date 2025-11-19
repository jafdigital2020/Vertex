<?php

namespace App\Helpers;

use App\Models\Addon;
use App\Models\BranchAddon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AddonsChecker
{
    /**
 
     *
     * @return \App\Models\User|null
     */
    protected static function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**

     *
     * @param  int  $addonId
     * @return bool
     */
    public static function hasAddon(int $addonId): bool
    {
        $authUser = self::authUser();

        if (! $authUser || ! $authUser->employmentDetail) {
            Log::debug('AddonsChecker: No auth user or employment detail', [
                'addon_id' => $addonId
            ]);
            return false;
        }

        // Get the addon to check its category
        $addon = Addon::find($addonId);

        if (! $addon) {
            Log::warning('AddonsChecker: Addon not found', [
                'addon_id' => $addonId
            ]);
            return false;
        }

        // Check if addon is active in branch_addons
        $isActive = BranchAddon::where('branch_id', $authUser->employmentDetail->branch_id)
            ->where('addon_id', $addonId)
            ->where('active', 1)
            ->exists();

        Log::debug('AddonsChecker: Checking addon access', [
            'addon_id' => $addonId,
            'addon_name' => $addon->name,
            'addon_category' => $addon->addon_category,
            'branch_id' => $authUser->employmentDetail->branch_id,
            'is_active' => $isActive,
            'user_id' => $authUser->id
        ]);

        // If addon category is 'upgrade' and not active in branch_addons, return false
        if ($addon->addon_category === 'upgrade' && !$isActive) {
            Log::info('AddonsChecker: Upgrade addon not active', [
                'addon_id' => $addonId,
                'addon_name' => $addon->name
            ]);
            return false;
        }

        // For 'addon' category, also check if it's active in branch_addons
        // This ensures both categories require active subscription
        return $isActive;
    }

    /**
 
     *
     * @param  int  $submoduleId
     * @return bool
     */
    public static function hasSubmodule(int $submoduleId): bool
    {
        $authUser = self::authUser();

        if (! $authUser || ! $authUser->employmentDetail) {
            return false;
        }

        $addons = BranchAddon::where('branch_id', $authUser->employmentDetail->branch_id)
            ->where('active', 1)
            ->pluck('addon_id');

        $addonRecords = Addon::whereIn('id', $addons)->get();

        foreach ($addonRecords as $addon) {
            if ($addon->submodule_ids) {
                $submodules = explode(',', $addon->submodule_ids);
                if (in_array($submoduleId, $submodules)) {
                    return true;
                }
            }
        }

        return false;
    }
}
