<?php
return [
	'settings' => [
		'displayErrorDetails' => false, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header

		// API general settings
		'api' => [
			'defaultPageSize' => 10,
			'maxPageSize' => 100,
		],

		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../../templates/',
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
//			'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../../logs/app.log',
			'path' => 'php://stdout', // FIXME: Configure real logging for Beanstalk
			'level' => \Monolog\Logger::DEBUG,
		],

		// Database settings
		'determineRouteBeforeAppMiddleware' => false,
		'db' => [
			'driver' => 'mysql',
			'host' => 'localhost',
			'database' => 'database',
			'username' => 'username',
			'password' => 'password',
			'charset' => 'latin1',
			'collation' => 'latin1_swedish_ci',
			'prefix' => '',
		]
	],
];
