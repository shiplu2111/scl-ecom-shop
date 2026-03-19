<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Send a standard success JSON response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data = [], string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null
        ], $code);
    }

    /**
     * Send a standard error JSON response
     *
     * @param string $message
     * @param mixed $errors
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => (object)[],
            'errors' => $errors
        ], $code);
    }
}
