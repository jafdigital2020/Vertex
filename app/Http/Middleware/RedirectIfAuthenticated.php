<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check() || Auth::guard('global')->check()) {
          
            if(Auth::guard('web')->check()){
              return redirect()->route('employee-dashboard');  
            }else{
              if (Auth::guard('global')->user()->global_role->global_role_name == 'super_admin'){ 
                 return redirect()->route('superadmin-dashboard');     
              }else{
                 return redirect()->route('admin-dashboard');     
              }
            }
        }

        return $next($request);
    }
}
