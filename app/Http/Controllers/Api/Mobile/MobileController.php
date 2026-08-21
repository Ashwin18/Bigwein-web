<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class MobileController extends Controller
{
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        ?array $meta = null,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'error' => false,
            'message' => $message,
            'data' => $data ?? (object) [],
            'meta' => $meta,
        ], $status);
    }

    protected function error(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'errors' => (object) $errors,
        ], $status);
    }
}
