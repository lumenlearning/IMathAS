<?php

namespace OHM\Api\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class BaseApiController
{
	private $defaultPageSize;
	private $maxPageSize;

	/**
	 * BaseApiController constructor.
	 * @param Container $container
	 */
	public function __construct($container)
	{
		$apiSettings = $container->get('settings')->get('api');
		$this->defaultPageSize = $apiSettings['defaultPageSize'];
		$this->maxPageSize = $apiSettings['maxPageSize'];
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

		$pageSize = $request->getQueryParam('per_page', $this->defaultPageSize);
		if ($this->maxPageSize < $pageSize) {
			$pageSize = $this->maxPageSize;
		}

		return array($pageNum, $pageSize);
	}
}
