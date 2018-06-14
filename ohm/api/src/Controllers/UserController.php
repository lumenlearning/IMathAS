<?php

namespace OHM\Api\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

use OHM\Models\User;

class UserController extends BaseApiController
{
	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return null
	 */
	public function findAll($request, $response, $args)
	{
		list($pageNum, $pageSize) = $this->getPaginationArgs($request);

		$users = User::take($pageSize)->skip($pageSize * $pageNum);
//		 conditional sql goes here
		$users = $users->get();

		return $response->withJson($users);
	}
}
