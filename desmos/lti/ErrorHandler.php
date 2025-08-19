<?php

namespace Desmos\Lti;

use Sanitize;
use Throwable;

class ErrorHandler
{
    /**
     * Output an error page.
     *
     * @param array $errors An array of error messages for the user.
     */
    public static function reportErrors(array $errors): void
    {
        global $imasroot;

        printf('<img src="%s/ohm/img/ohm-logo-color-400.png"/>', $imasroot);
        echo '<p>The following LTI launch errors were encountered:</p>';
        echo '<ul>';
        foreach ($errors as $error) {
            printf('<li>%s</li>', Sanitize::encodeStringForDisplay($error));
        }
        echo '</ul>';
    }

    /**
     * Warning handler.
     *
     * Currently, warnings are only logged and not displayed to the user.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return bool
     */
    public static function errorHandler(int $errno, string $errstr, string $errfile,
                                        int $errline, array $errcontext): bool
    {
        if (E_WARNING == $errno || E_ERROR == $errno) {
            error_log(sprintf('Caught error in %s:%s -- %s',
                $errfile, $errline, $errstr));

        }
        // True = Don't execute the PHP internal error handler.
        // False = Populate $php_errormsg.
        // Reference: https://secure.php.net/manual/en/function.set-error-handler.php
        return true;
    }

    /**
     * Exception handler.
     *
     * Exception messages are displayed to the user and page execution is halted.
     *
     * @param Throwable $t
     */
    public static function exceptionHandler(Throwable $t): void
    {
        self::reportErrors(array($t->getMessage()));

        error_log(sprintf('Caught exception in %s:%d -- %s',
            $t->getFile(), $t->getLine(), $t->getMessage()));

        exit;
    }
}
