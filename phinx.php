<?php

$ohmApiSettings = require_once(__DIR__ . '/ohm/api/src/configs/settings.php');

print_r($ohmApiSettings['db']);

return [
	'paths' => [
		'migrations' => __DIR__ . '/ohm/db/migrations',
		'seeds' => __DIR__ . '/ohm/db/seeds',
	],
	'environments' => [
		'default_environment' => 'development',
		'default_migration_table' => 'ohm_phinxlog',
		'production' => [
			'adapter' => 'mysql',
			'host' => $ohmApiSettings['db']['host'],
			'name' => $ohmApiSettings['db']['database'],
			'user' => $ohmApiSettings['db']['username'],
			'pass' => $ohmApiSettings['db']['password'],
			'port' => 3306,
			'charset' => $ohmApiSettings['db']['charset'],
		],
		'development' => [
			'adapter' => 'mysql',
			'host' => $ohmApiSettings['db']['host'],
			'name' => $ohmApiSettings['db']['database'],
			'user' => $ohmApiSettings['db']['username'],
			'pass' => $ohmApiSettings['db']['password'],
			'port' => 3306,
			'charset' => $ohmApiSettings['db']['charset'],
		],
		'testing' => [
			'adapter' => 'mysql',
			'host' => $ohmApiSettings['db']['host'],
			'name' => $ohmApiSettings['db']['database'],
			'user' => $ohmApiSettings['db']['username'],
			'pass' => $ohmApiSettings['db']['password'],
			'port' => 3306,
			'charset' => $ohmApiSettings['db']['charset'],
		],
	],
];

