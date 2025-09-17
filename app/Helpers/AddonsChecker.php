<?php

namespace App\Helpers;

use App\Models\Addon;
use App\Models\BranchAddon;
use Illuminate\Support\Facades\Auth;

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
        return Auth::guard('web')->user();
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
            return false;
        }

        return BranchAddon::where('branch_id', $authUser->employmentDetail->branch_id)
            ->where('addon_id', $addonId)
            ->where('active', 1)
            ->exists();
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
