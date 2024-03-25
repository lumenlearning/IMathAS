<?php

use OHM\Api\Controllers\GroupController;
use OHM\Api\Controllers\LtiCredentialController;
use OHM\Api\Controllers\UserController;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

// Routes

/* @var AppFactory $app */
$app->redirect('[/]', '/', 302); // Send curious users to OHM's root URL.

$app->group('/v1', function (RouteCollectorProxy $group) {
    $group->group('/users', function (RouteCollectorProxy $group) {
        $group->get('[/]', UserController::class . ':findAll');
	});

    $group->group('/groups', function (RouteCollectorProxy $group) {
        $group->get('[/]', GroupController::class . ':findAll');
        $group->get('/{id}', GroupController::class . ':find');
        $group->post('[/]', GroupController::class . ':create');
        $group->delete('/{id}', GroupController::class . ':delete');
        $group->put('[/{id}]', GroupController::class . ':update');

        $group->group('/{groupId}/lti_credentials', function (RouteCollectorProxy $group) {
            $group->get('[/]', LtiCredentialController::class . ':findAll');
            $group->get('/{id}', LtiCredentialController::class . ':find');
            $group->post('[/]', LtiCredentialController::class . ':create');
            $group->delete('/{id}', LtiCredentialController::class . ':delete');
            $group->put('[/{id}]', LtiCredentialController::class . ':update');
		});
	});
});
