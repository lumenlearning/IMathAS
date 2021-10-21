<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class ApiBaseController extends BaseController
{
    // Pagination settings
    private $defaultPageSize = 10;
    private $maxPageSize = 100;

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

    /**
     * Get pagination arguments from the request URL.
     *
     * @param Request $request
     * @return array Page number, page size
     */
    protected function getPaginationArgs(Request $request)
    {
        $pageNum = $request->get('page', 0);
        if ($pageNum < 0) {
            $pageNum = 0;
        }

        $pageSize = $request->get('per_page', $this->defaultPageSize);
        if ($this->maxPageSize < $pageSize) {
            $pageSize = $this->maxPageSize;
        }

        return array($pageNum, $pageSize);
    }
}
