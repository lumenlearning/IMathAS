<?php

namespace Desmos;

require_once(__DIR__ . '/../vendor/autoload.php');

use Desmos\Lti\BasicLti;
use Desmos\Lti\ErrorHandler;

set_exception_handler(array('Desmos\Lti\ErrorHandler', 'exceptionHandler'));
$init_skip_csrfp = true;
require_once(__DIR__ . "/../init_without_validate.php");
unset($init_skip_csrfp);

$blti = new BasicLti($_REQUEST, $GLOBALS['DBH']);

// Ensure all required LTI data was provided.
$launchDataErrors = $blti->hasValidLtiData();
if (!empty($launchDataErrors)) {
    ErrorHandler::reportErrors($launchDataErrors);
    exit;
}

// Authenticate LTI credentials, get OHM user info.
$blti->authenticate();
$blti->assignOhmDataFromLaunch();

header(
    sprintf('Location: %s/course/itemview.php?type=Desmos&cid=%d&id=%d',
        $GLOBALS['basesiteurl'], $blti->getOhmCourseId(), $blti->getDesmosItemId())
);
exit;


/*
 * DEBUG BEGIN
 * FIXME: Delete this after dev / testing!
 */
?>
    <h1>LTI launch successful!</h1>

    <li>You are requesting assessment item
        ID: <?php echo $blti->getItemId(); ?></li>
    <li>Desmos ID: <?php echo $blti->getDesmosItemId(); ?></li>
    <li>Desmos item: <?php echo $blti->getDesmosTitle(); ?></li>
    <li>Course ID: <?php echo $blti->getOhmCourseId(); ?></li>
    <li>Course Name: <?php echo $blti->getOhmCourseName(); ?></li>
    <?php

$blti->debugOutput();
/*
 * DEBUG END
 */
