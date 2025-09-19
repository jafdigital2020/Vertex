<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{ 
    public function register(): void
    {
        //
    } 

    public static function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }

        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }

        return null;
    }

    public function boot()
    {
        View::composer('*', function ($view) {
            $roleData = [
                "role_id"             => null,
                "menu_ids"            => [],
                "module_ids"          => [],
                "user_permission_ids" => [],
                "status"              => null,
            ];

            $user = self::authUser();

            if ($user) { 
                if (Auth::guard('global')->check()) {
                    $roleData = [
                        "role_id"             => 'global_user',
                        "menu_ids"            => [],
                        "module_ids"          => [],
                        "user_permission_ids" => [],
                        "status"              => null,
                    ];
                } 
                elseif (Auth::guard('web')->check()) {
                    $roleData = $user->role_data ?? $roleData;
                }
            }

            $view->with('role_data', $roleData);
        });
    }
}
