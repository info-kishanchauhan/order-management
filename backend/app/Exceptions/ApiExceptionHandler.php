<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public function renderApi(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => 422,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof InvalidOrderStatusTransitionException) {
            return $e->render($request);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthenticated.',
                'errors' => ['token' => ['Authentication required.']],
            ], 401);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status' => 404,
                'message' => 'Resource not found.',
                'errors' => ['id' => ['No matching record was found.']],
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 404,
                'message' => 'Resource not found.',
                'errors' => ['route' => ['Endpoint not found.']],
            ], 404);
        }

        return null;
    }
}
