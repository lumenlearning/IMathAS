<?php

namespace OHM;

use Exception;
use Throwable;

class ErrorHandler
{
    const ERROR_CODES = [
        E_ERROR => "E_ERROR",
        E_WARNING => "E_WARNING",
        E_PARSE => "E_PARSE",
        E_NOTICE => "E_NOTICE",
        E_CORE_ERROR => "E_CORE_ERROR",
        E_CORE_WARNING => "E_CORE_WARNING",
        E_COMPILE_ERROR => "E_COMPILE_ERROR",
        E_COMPILE_WARNING => "E_COMPILE_WARNING",
        E_USER_ERROR => "E_USER_ERROR",
        E_USER_WARNING => "E_USER_WARNING",
        E_USER_NOTICE => "E_USER_NOTICE",
        E_STRICT => "E_STRICT",
        E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
        E_DEPRECATED => "E_DEPRECATED",
        E_USER_DEPRECATED => "E_USER_DEPRECATED",
        E_ALL => "E_ALL"
    ];

    /**
     * Error handler for PHP errors.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return bool
     */
    public static function phpErrorHandler(int $errno, string $errstr, string $errfile,
                                           int $errline, array $errcontext = []): bool
    {
        global $configEnvironment;

        $exception = new Exception; // This allows us to get a current stack trace.

        $humanErrorCode = self::ERROR_CODES[$errno] ?? $errno;
        $loggableErrorMessage = sprintf(
            "Caught PHP error (%s): %s,
in file: %s:%d
stack trace:
%s",
            $humanErrorCode, $errstr,
            $errfile, $errline,
            $exception->getTraceAsString(),
        );
        error_log($loggableErrorMessage);

        if (!empty($configEnvironment) && 'development' == $configEnvironment) {
            printf('<pre>%s</pre>', $loggableErrorMessage);
        }

        // True = Don't execute the PHP internal error handler.
        // False = Allow the default PHP internal error handler to execute.
        // Reference: https://secure.php.net/manual/en/function.set-error-handler.php
        return false;
    }

    /**
     * Exception handler for uncaught exceptions.
     *
     * @param Throwable $throwable The uncaught exception.
     */
    public static function phpExceptionHandler(Throwable $throwable): void
    {
        global $configEnvironment;

        $loggableErrorMessage = sprintf(
            "Uncaught Exception: (%d) %s,
in file: %s:%d,
stack trace:
%s",
            $throwable->getCode(), $throwable->getMessage(),
            $throwable->getFile(), $throwable->getLine(),
            $throwable->getTraceAsString(),
        );
        error_log($loggableErrorMessage);

        if (!empty($configEnvironment) && 'development' == $configEnvironment) {
            printf('<pre>%s</pre>', $loggableErrorMessage);
        }
    }
}
