<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return a success JSON response.
     */
    protected function success(mixed $data = null, string $message = null, int $code = 200): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a success JSON response with pagination.
     */
    protected function successWithPagination($paginator, string $message = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * Return an error JSON response.
     */
    protected function error(string $message, string $code = 'ERROR', int $httpCode = 400, array $details = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $httpCode);
    }

    /**
     * Return a validation error response.
     */
    protected function validationError(array $errors): \Illuminate\Http\JsonResponse
    {
        return $this->error(
            'The given data was invalid.',
            'VALIDATION_ERROR',
            422,
            $errors
        );
    }

    /**
     * Return a not found error response.
     */
    protected function notFound(string $message = 'Resource not found.'): \Illuminate\Http\JsonResponse
    {
        return $this->error($message, 'NOT_FOUND', 404);
    }

    /**
     * Return an unauthorized error response.
     */
    protected function unauthorized(string $message = 'Unauthorized.'): \Illuminate\Http\JsonResponse
    {
        return $this->error($message, 'UNAUTHORIZED', 401);
    }

    /**
     * Return a forbidden error response.
     */
    protected function forbidden(string $message = 'Forbidden.'): \Illuminate\Http\JsonResponse
    {
        return $this->error($message, 'FORBIDDEN', 403);
    }
}
