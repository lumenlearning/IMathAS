<?php

namespace OHM\Api\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use OHM\Models\Session;

class ValidateUser
{
	protected $container;

	/**
	 * ValidateUser constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct($container)
	{
		$this->container = $container;
	}

	/**
	 * @param  ServerRequestInterface $request PSR7 request
	 * @param  ResponseInterface $response PSR7 response
	 * @param  callable $next Next middleware
	 *
	 * @return ResponseInterface
	 */
	public function __invoke($request, $response, $next)
	{
		$sessionId = session_id();

		$session = Session::where('sessionid', $sessionId)->first();
		if (is_null($session)) {
			return $response->withStatus(401)
				->withJson([
					'status' => 401,
					'message' => 'Unauthorized',
				]);
		}

//		$response->getBody()->write('BEFORE');
		$response = $next($request, $response);
//		$response->getBody()->write('AFTER');

		return $response;
	}
}
