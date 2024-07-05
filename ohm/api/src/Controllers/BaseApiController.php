<?php

namespace OHM\Api\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BaseApiController
{
	private $defaultPageSize;
	private $maxPageSize;

	/**
	 * BaseApiController constructor.
     * @param ContainerInterface $container
	 */
    public function __construct(ContainerInterface $container)
	{
        $settings = $container->get('settings');
        $this->defaultPageSize = $settings['api']['defaultPageSize'];
        $this->maxPageSize = $settings['api']['maxPageSize'];
	}

	/**
	 * Get pagination arguments from the request URL.
	 *
	 * @param Request $request
	 * @return array Page number, page size
	 */
    protected function getPaginationArgs(Request $request): array
	{
        $queryParams = $request->getQueryParams();

        $pageNum = $queryParams['page'] ?? 0;
		if ($pageNum < 0) {
			$pageNum = 0;
		}

        $pageSize = $queryParams['per_page'] ?? $this->defaultPageSize;
		if ($this->maxPageSize < $pageSize) {
			$pageSize = $this->maxPageSize;
		}

		return array($pageNum, $pageSize);
	}
}
