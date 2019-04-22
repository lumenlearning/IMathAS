<?php

/**
 * This entire file is require'd as-is in /init.php.
 */

require_once(__DIR__ . '/../vendor/autoload.php');

// Development dependencies
if (isset($GLOBALS['configEnvironment']) && 'development' == $GLOBALS['configEnvironment']) {
    require_once(__DIR__ . '/../c3.php');
}

