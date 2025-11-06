<?php

use App\Http\Middleware\IsSuperAdmin;
use Illuminate\Foundation\Application;
use App\Http\Middleware\CheckSubscription;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // ✅ Ensure API routes are included
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ API Middleware (Disables CSRF for API Routes)
        $middleware->appendToGroup('api', EnsureFrontendRequestsAreStateful::class);
        $middleware->appendToGroup('api', \Illuminate\Routing\Middleware\ThrottleRequests::class);
        $middleware->appendToGroup('api', \Illuminate\Routing\Middleware\SubstituteBindings::class);
        $middleware->appendToGroup('web', \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
       $middleware->validateCsrfTokens(except: [
<<<<<<< Updated upstream
        '/cdata',
        '/cdata.aspx',
        '/iclock/cdata',
        '/iclock/cdata.aspx',
=======
        '/api/zkapi/cdata',
        '/api/zkapi/cdata.aspx',
        '/api/zkapi/getrequest',
        '/api/zkapi/getrequest.aspx',
        '/api/zkapi/devicecmd',
        '/api/zkapi/devicecmd.aspx',
        '/api/iclock/cdata',
        '/api/iclock/cdata.aspx',
        '/api/iclock/getrequest',
        '/api/iclock/getrequest.aspx',
        '/api/iclock/devicecmd',
        '/api/iclock/devicecmd.aspx',
        '/api/cdata',
        '/api/cdata.aspx',
>>>>>>> Stashed changes
      ]);
        $middleware->alias([
            'check.subscription' => CheckSubscription::class,
            'isSuperAdmin' => \App\Http\Middleware\IsSuperAdmin::class,
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withCommands([
        App\Console\Commands\ActivateSalaries::class,
    ])
    ->withSchedule(function ($schedule) {
        require base_path('routes/scheduler.php');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
