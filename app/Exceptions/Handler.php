<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException; // Add this import
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $exception->getHeaders()['Retry-After'] ?? 60
            ], 429);
        }

        // Add CSRF Token Mismatch handling
        if ($exception instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'title'  => 'Session Expired',
                    'message'=> 'Your session has expired. Please refresh the page and try again. If the issue persists, please log in again.'
                ], 419);
            }

            return redirect()->back()
                ->with('error', 'Your session has expired. Please refresh the page and try again. If the issue persists, please log in again.')
                ->withInput();
        }

        return parent::render($request, $exception);
    }
}
