<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class Controller extends BaseController
{
    use AuthorizesRequests;

    public const HTTP_OK = 200;

    public const HTTP_CREATED = 201;

    public const HTTP_NO_CONTENT = 204;

    public const HTTP_BAD_REQUEST = 400;

    public const HTTP_UNAUTHORIZED = 401;

    public const HTTP_FORBIDDEN = 403;

    public const HTTP_NOT_FOUND = 404;

    public const HTTP_CONFLICT = 409;

    public const HTTP_UNPROCESSABLE_ENTITY = 422;

    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * @param  array<string, mixed>|\Illuminate\Support\MessageBag  $errors
     */
    public function validationFailed(string $message, array|\Illuminate\Support\MessageBag $errors): JsonResponse
    {
        $errorsPayload = $errors instanceof \Illuminate\Support\MessageBag
            ? $errors->toArray()
            : $errors;

        return Response::json([
            'status' => self::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $message,
            'errors' => $errorsPayload,
        ], self::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param  mixed  $data
     * @param  array<string, mixed>|null  $meta
     */
    public function actionSuccess(string $message, mixed $data = null, int $statusCode = self::HTTP_OK, ?array $meta = null): JsonResponse
    {
        $response = [
            'status' => $statusCode,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return Response::json($response, $statusCode);
    }

    /**
     * @param  mixed  $data
     */
    public function actionFailure(string $message, mixed $data = null, int $statusCode = self::HTTP_CONFLICT): JsonResponse
    {
        $response = [
            'status' => $statusCode,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return Response::json($response, $statusCode);
    }

    /**
     * @param  mixed  $result
     */
    public function sendResponse(mixed $result, ?string $message = null, int $code = 200): JsonResponse
    {
        return Response::json([
            'success' => $code >= 200 && $code < 300,
            'status' => $code,
            'data' => $result,
            'message' => $message,
        ], $code);
    }

    public function notFoundList(Request $request, int $perPage = 15): JsonResponse
    {
        $emptyCollection = collect([]);
        $paginator = new LengthAwarePaginator(
            $emptyCollection,
            0,
            $perPage,
            1,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $this->actionSuccess('No records found', $this->customizingResponseData($paginator));
    }

    /**
     * @return array{data: mixed, pagination: array<string, mixed>}
     */
    public function customizingResponseData(LengthAwarePaginator $list): array
    {
        return [
            'data' => $list->items(),
            'pagination' => [
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(),
                'total' => $list->total(),
                'from' => $list->firstItem(),
                'to' => $list->lastItem(),
                'has_more_pages' => $list->hasMorePages(),
                'prev_page_url' => $list->previousPageUrl(),
                'next_page_url' => $list->nextPageUrl(),
            ],
        ];
    }

    /**
     * @param  mixed  $data
     */
    public function serverError(string $message = 'Internal server error', mixed $data = null): JsonResponse
    {
        return $this->actionFailure($message, $data, self::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->actionFailure($message, null, self::HTTP_NOT_FOUND);
    }

    public function unauthorized(string $message = 'Unauthorized access'): JsonResponse
    {
        return $this->actionFailure($message, null, self::HTTP_UNAUTHORIZED);
    }

    public function forbidden(string $message = 'Access forbidden'): JsonResponse
    {
        return $this->actionFailure($message, null, self::HTTP_FORBIDDEN);
    }
}
