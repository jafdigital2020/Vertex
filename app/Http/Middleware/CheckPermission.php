<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $subModuleId)
    {
        $permissions = PermissionHelper::get((int)$subModuleId);

        if (empty($permissions)) {
           return response()->view('errors.access_denied', [], 403);
        }

        return $next($request);
    }
}
