<?php

// Exclude notices, warnings, and deprecations from our error handler
// because there are currently an excessive amount of them in OHM.
$errorsHandled = E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED;

$ohmErrorHandler = new OHM\ErrorHandler();
set_error_handler([$ohmErrorHandler, 'phpErrorHandler'], $errorsHandled);
set_exception_handler([$ohmErrorHandler, 'phpExceptionHandler']);
