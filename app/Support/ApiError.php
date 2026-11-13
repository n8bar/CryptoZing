<?php

namespace App\Support;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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

    /** Any exception on an api/* request, in the format above; null leaves a ready response alone. */
    public static function fromException(Throwable $e): ?JsonResponse
    {
        return match (true) {
            $e instanceof HttpResponseException => null,
            $e instanceof ValidationException => self::response('validation', $e->validator->errors()->first($field = array_key_first($e->errors())), 422, $field),
            $e instanceof NotFoundHttpException => self::response('not_found', $e->getPrevious() instanceof ModelNotFoundException ? 'No such invoice.' : 'No such endpoint.', 404),
            $e instanceof MethodNotAllowedHttpException => self::response('method_not_allowed', 'This endpoint does not take that method.', 405, headers: $e->getHeaders()),
            $e instanceof HttpExceptionInterface && $e->getStatusCode() === 503 => self::unavailable(),
            default => self::response('server_error', 'Something went wrong on our side. Try again shortly.', 500),
        };
    }

    public static function unavailable(string $message = 'CryptoZing cannot take this request right now. Try again shortly.'): JsonResponse
    {
        return self::response('unavailable', $message, 503);
    }
}
