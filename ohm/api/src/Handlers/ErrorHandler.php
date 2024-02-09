<?php

namespace OHM\Api\Handlers;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Ramsey\Uuid\Uuid;

/**
 * Class ErrorHandler For application-wide errors
 * @package OHM\Api\Handlers
 * @see https://www.slimframework.com/docs/v3/handlers/error.html
 */
class ErrorHandler extends \Slim\Handlers\Error
{
	protected $errorLogger;

	/**
	 * ErrorHandler constructor.
	 * @param Container $container
	 */
	public function __construct($container)
	{
		$this->errorLogger = $container->get('errorLogger');
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param \Exception $error
	 * @return ResponseInterface
	 */
	public function __invoke($request, $response, $error)
	{
		$errorGuid = Uuid::uuid4()->toString();

		$extraLog = array('error_guid' => $errorGuid);
		$this->errorLogger->critical($error->getTraceAsString(), $extraLog);
		$this->errorLogger->critical($error->getMessage(), $extraLog);

		$content = array(
			'errors' => array(
				'Internal server error.',
				'Please see server logs and reference error_guid.'
			),
			'error_guid' => $errorGuid,
		);
        $payload = json_encode($content);
        $response->getBody()->write($payload);
		return $response->withStatus(500)
            ->withHeader('Content-Type', 'application/json');
	}
}
