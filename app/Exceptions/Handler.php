<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            if (
                !$request->is('api/mobile/*')
                || $e instanceof AuthenticationException
                || $e instanceof HttpResponseException
            ) return null;

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = match ($status) {
                    403 => 'Forbidden.',
                    404 => 'Resource not found.',
                    405 => 'Method not allowed.',
                    default => $status >= 500 ? 'Something went wrong. Please try again.' : 'Request could not be completed.',
                };

                return response()->json([
                    'error' => true,
                    'message' => $message,
                    'errors' => (object) [],
                ], $status);
            }

            return response()->json([
                'error' => true,
                'message' => 'Something went wrong. Please try again.',
                'errors' => (object) [],
            ], 500);
        });
    }


    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/mobile/*')) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthenticated.',
                'errors' => (object) [],
            ], 401);
        }

        return $request->expectsJson()
            ? response()->json(['error' => true, 'message' => 'User is not authenticated'], 401)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

}
