<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse(array $data, $responseCode = 200)
    {
        return response()->json($data, $responseCode);
    }

    public function errorResponse(array $data, $responseCode = 500)
    {
        return response()->json($data, $responseCode);
    }
}
