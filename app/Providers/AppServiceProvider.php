<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{ 
    public function register(): void
    {
         
    } 
    public function boot()
{
    View::composer('*', function ($view) {
        if (Session::has('role_data')) {
            $roleData = Session::get('role_data');
        } elseif (Auth::check()) { 
            $roleData = [
                "role_id" => 'global_user',
                "menu_ids" => [], 
                "module_ids" => [],
                "user_permission_ids" => [],
            ];
        } else { 
            $roleData = [
                "role_id" => null,
                "menu_ids" => [],
                "module_ids" => [],
                "user_permission_ids" => [],
            ];
        }

        $view->with('role_data', $roleData);
    });
}

} 
