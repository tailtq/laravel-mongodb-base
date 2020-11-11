<?php

namespace App\Traits;

trait ResponseTrait
{
    public function success($data)
    {
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Ok',
        ]);
    }

    public function error($message, $code)
    {
        return response()->json([
            'status' => false,
            'data' => null,
            'message' => $message
        ], $code);
    }
}
