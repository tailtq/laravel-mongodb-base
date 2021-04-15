<?php

namespace Infrastructure\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    /**
     * @param array $data
     * @return JsonResponse
     */
    public function success($data = []): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Ok',
        ]);
    }

    /**
     * @param $message
     * @param $code
     * @return JsonResponse
     */
    public function error($message, $code): JsonResponse
    {
        return response()->json([
            'status' => false,
            'data' => null,
            'message' => $message
        ], $code);
    }
}
