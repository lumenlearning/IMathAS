<?php

require_once(__DIR__ . '/../vendor/autoload.php');

// Development dependencies
if (isset($GLOBALS['configEnvironment']) && 'development' == $GLOBALS['configEnvironment']) {
	require_once(__DIR__ . '/../c3.php');
}

