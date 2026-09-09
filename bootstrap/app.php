<?php

use App\Support\Api\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware aliases and groups will be registered here as needed.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApiRequest = static fn (Request $request): bool => $request->is('api/*');

        $exceptions->shouldRenderJsonWhen(
            static fn (Request $request): bool => $isApiRequest($request) || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'The given data was invalid.',
                status: 422,
                errors: $exception->errors(),
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Unauthenticated.',
                status: 401,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'You are not authorized to perform this action.',
                status: 403,
            );
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'Resource not found.',
                status: 404,
            );
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $status = $exception->getStatusCode();
            $message = match ($status) {
                404 => 'API endpoint not found.',
                405 => 'HTTP method not allowed for this endpoint.',
                429 => 'Too many requests. Please try again later.',
                default => $status >= 500
                    ? 'An unexpected server error occurred.'
                    : ($exception->getMessage() !== '' ? $exception->getMessage() : 'Request failed.'),
            };

            return ApiResponse::error(
                message: $message,
                status: $status,
                headers: $exception->getHeaders(),
            );
        });

        $exceptions->render(function (\Throwable $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                message: 'An unexpected server error occurred.',
                status: 500,
            );
        });
    })->create();
