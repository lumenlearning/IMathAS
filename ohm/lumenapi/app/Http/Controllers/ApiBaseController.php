<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class ApiBaseController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param array $errors
     * @return JsonResponse
     */
    public function BadRequest(array $errors): JsonResponse
    {
        return response()->json([
            'errors' => $errors,
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}


