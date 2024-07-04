<?php

namespace OHM\Api\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

use OHM\Models\Session;

/**
 * Class ValidateUser Attempts to validate a user's OHM session.
 *
 * If this fails, the request will be stopped with an HTTP 401 response.
 *
 * Note: There is currently no code path to this class. See middleware.php.
 *
 * @package OHM\Api\Middleware
 */
class ValidateUser
{
	protected ContainerInterface $container;

	/**
	 * ValidateUser constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param  Request $request PSR-7 request
	 * @param  RequestHandler $handler PSR-15 request handler
	 *
	 * @return Response
	 */
	public function __invoke(Request $request, RequestHandler $handler): Response
	{
		$jwt = $request->getAttribute('jwt');

        $response = $handler->handle($request);

		if (!$this->isValidOhmSession($request) && empty($jwt)) {
            $payload = json_encode(['errors' => ['Please login or provide a valid API token.']]);
            $response->getBody()->write($payload);

			return $response
                ->withHeader('Content-Type', 'application/json');
		}

		return $response;
	}

	/**
	 * Determine if this request is associated with a valid OHM user session.
	 *
	 * @param Request $request
	 * @return bool
	 */
	private function isValidOhmSession(Request $request): bool
	{
		$sessionId = session_id();
		$session = Session::where('sessionid', $sessionId)->first();

		return is_null($session) ? false : true;
	}
}
