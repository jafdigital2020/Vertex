<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int,class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // Catch CSRF token mismatch and return a user-friendly message
        if ($exception instanceof TokenMismatchException) {
            if ($request->wantsJson()) {
                // For AJAX requests, return a JSON response
                return response()->json([
                    'message' => 'Your session has expired. Please try again.'
                ], 419);
            }

            // For regular page loads, redirect back with an error message
            return redirect()->back()->withErrors(['message' => 'Your session has expired. Please try again.']);
        }

        return parent::render($request, $exception);
    }
}

