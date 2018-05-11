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
		$jwt = $request->getAttribute('jwt');

		if (!$this->isValidOhmSession($request) && empty($jwt)) {
			return $response->withStatus(401)
				->withJson(['errors' => ['Please login or provide a valid API token.']]);
		}

//		$response->getBody()->write('BEFORE');
		$response = $next($request, $response);
//		$response->getBody()->write('AFTER');

		return $response;
	}

	/**
	 * Determine if this request is associated with a valid OHM user session.
	 *
	 * @param ServerRequestInterface $request
	 * @return bool
	 */
	private function isValidOhmSession($request)
	{
		$sessionId = session_id();
		$session = Session::where('sessionid', $sessionId)->first();

		return is_null($session) ? false : true;
	}
}
