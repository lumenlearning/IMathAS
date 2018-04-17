<?php

namespace OHM\Api\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

use OHM\Models\User;

class UserController
{
	private $defaultPageSize;
	private $maxPageSize;

	/**
	 * UserController constructor.
	 * @param Container $container
	 */
	public function __construct($container)
	{
		$apiSettings = $container->get('settings')->get('api');
		$this->defaultPageSize = $apiSettings['defaultPageSize'];
		$this->maxPageSize = $apiSettings['maxPageSize'];
	}

	/**
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return null
	 */
	public function showUser($request, $response, $args)
	{
		list($pageNum, $pageSize) = $this->getPaginationArgs($request);

		$users = User::take($pageSize)->skip($pageSize * $pageNum);
//		 conditional sql goes here
		$users = $users->get();

		return $response->withJson($users);
	}

	/**
	 * Get pagination arguments from the request URL.
	 *
	 * @param Request $request
	 * @return array Page number, page size
	 */
	protected function getPaginationArgs($request)
	{
		$pageNum = $request->getQueryParam('page', 0);
		if ($pageNum < 0) {
			$pageNum = 0;
		}

		$pageSize = $request->getQueryParam('size', $this->defaultPageSize);
		if ($this->maxPageSize < $pageSize) {
			$pageSize = $this->maxPageSize;
		}

		return array($pageNum, $pageSize);
	}
}
