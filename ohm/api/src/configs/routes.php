<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
	// Sample log message
	$this->logger->info("Slim-Skeleton '/' route");

	// Render index view
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->group('/v1', function () {
	$this->group('/users', function () {
		$this->get('[/]', \OHM\Api\Controllers\UserController::class . ':findAll');
	});

	$this->group('/groups', function () {
		$this->get('[/]', \OHM\Api\Controllers\GroupController::class . ':findAll');
		$this->post('[/]', \OHM\Api\Controllers\GroupController::class . ':create');
		$this->delete('/{id}', \OHM\Api\Controllers\GroupController::class . ':delete');
		$this->put('[/{id}]', \OHM\Api\Controllers\GroupController::class . ':update');
	});
});
