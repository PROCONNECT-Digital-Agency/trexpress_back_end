<?php

namespace App\Traits;

use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Success Response.
     *
     * @param string $message
     * @param mixed|null $data
     * @return JsonResponse
     */
    public function successResponse(string $message = '', $data = null): JsonResponse
    {
        return new JsonResponse([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], Response::HTTP_OK);
    }

    /**
     * Error Response.
     *
     * @param string $statusCode
     * @param string $message
     * @param int $httpCode
     * @return JsonResponse
     */
    public function errorResponse(string $statusCode, string $message = '', int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return new JsonResponse([
            'status' => false,
            'statusCode' => $statusCode,
            'message' => $message
        ], $httpCode);
    }

    public function requestErrorResponse(string $statusCode, string $message = '', array $params = [], int $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return new JsonResponse([
            'status' => false,
            'statusCode' => $statusCode,
            'message' => $message,
            'params' => $params
        ], $httpCode);
    }

    /**
     * @param array $result = ['code' => 200]
     * @return JsonResponse
     */
    public function onErrorResponse(array $result = []): JsonResponse
    {
        $code = data_get($result, 'code', ResponseError::ERROR_101);

        $httpDefault = $code === ResponseError::ERROR_404 ? Response::HTTP_NOT_FOUND : Response::HTTP_BAD_REQUEST;

        $http = data_get($result, 'http', $httpDefault);

        $data = is_array(data_get($result, 'data')) ? data_get($result, 'data') : [];

        $message = $code === ResponseError::ERROR_101 ?
            __('errors.' . ResponseError::ERROR_101, $data, request('lang')) :
            trans('errors.' . $code, $data, request('lang'));

        return $this->errorResponse(
            $code,
            data_get($result, 'message', $message),
            $http
        );
    }
}
