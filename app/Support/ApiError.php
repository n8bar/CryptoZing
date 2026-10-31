<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * The developer API's error body: {"error": {"code", "message", "field"?}}.
 * Codes are listed in docs/API.md §7.
 */
class ApiError
{
    public static function response(string $code, string $message, int $status, ?string $field = null, array $headers = []): JsonResponse
    {
        $error = ['code' => $code, 'message' => $message];
        if ($field !== null) {
            $error['field'] = $field;
        }

        return response()->json(['error' => $error], $status, $headers);
    }
}
