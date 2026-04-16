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
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null
        ];

        if ($data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
            $resource = $data->resource;
            if ($resource instanceof \Illuminate\Pagination\AbstractPaginator || $resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $paginated = $data->response()->getData(true);
                $response['data'] = $paginated['data'];
                $response['links'] = $paginated['links'] ?? null;
                $response['meta'] = $paginated['meta'] ?? null;
            }
        } elseif ($data instanceof \Illuminate\Pagination\AbstractPaginator || $data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $paginated = $data->toArray();
            $response['data'] = $paginated['data'];
            unset($paginated['data']);
            $response['meta'] = $paginated;
        }

        return response()->json($response, $code);
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
