<?php

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    /**
     * Return the standard success envelope used by the mobile API.
     *
     * @param  array<string, mixed>|null  $meta
     * @param  array<string, string>  $headers
     */
    public static function success(
        mixed $data = null,
        string $message = 'Request completed successfully.',
        int $status = 200,
        ?array $meta = null,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $meta,
        ], $status, $headers);
    }

    /**
     * Return the standard error envelope used by the mobile API.
     *
     * @param  array<string, mixed>|null  $errors
     * @param  array<string, string>  $headers
     */
    public static function error(
        string $message,
        int $status = 400,
        ?array $errors = null,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => null,
        ], $status, $headers);
    }
}
