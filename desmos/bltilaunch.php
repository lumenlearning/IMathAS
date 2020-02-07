<?php

namespace Desmos;

require_once(__DIR__ . '/../vendor/autoload.php');

use Desmos\Lti\BasicLti;
use Desmos\Lti\ErrorHandler;

header('P3P: CP="ALL CUR ADM OUR"');
set_exception_handler(array('Desmos\Lti\ErrorHandler', 'exceptionHandler'));
$init_skip_csrfp = true;
require(__DIR__ . "/../init_without_validate.php");
unset($init_skip_csrfp);

$blti = new BasicLti($_REQUEST);

// Ensure all required LTI data was provided.
$launchDataErrors = $blti->hasValidLtiData();
if (!empty($launchDataErrors)) {
    ErrorHandler::reportErrors($launchDataErrors);
    exit;
}

// Authenticate LTI credentials, get OHM user info.
$blti->authenticate();
$blti->assignOhmUserFromLaunch();


?>
    <h1>LTI launch successful!</h1>

    <li>You are requesting assessment
        ID: <?php echo $_REQUEST['custom_place_aid']; ?></li>
    <?php

// FIXME: Delete this after dev/testing!
$blti->debugOutput();
