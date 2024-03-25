<?php

namespace OHM\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use OHM\Models\User;

class UserController extends BaseApiController
{
	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
     * @return Response
	 */
    public function findAll(Request $request, Response $response, $args): Response
	{
		list($pageNum, $pageSize) = $this->getPaginationArgs($request);

		$users = User::take($pageSize)->skip($pageSize * $pageNum);
//		 conditional sql goes here
		$users = $users->get();

        $payload = json_encode($users);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
	}
}
