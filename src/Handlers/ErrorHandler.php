<?php

namespace Debug\Handlers;

class ErrorHandler
{
    public static $type = null;
    public static function Handle($severity, $message, $filename, $lineno)
    {
        $l = error_reporting();
        if ( $l & $severity ) {
            switch ($severity) {
                case E_USER_ERROR:
                    $type = 'Fatal Error';
                    break;
                case E_USER_WARNING:
                case E_WARNING:
                    $type = 'Warning';
                    break;
                case E_USER_NOTICE:
                case E_NOTICE:
                case @E_STRICT:
                    $type = 'Notice';
                    break;
                case @E_RECOVERABLE_ERROR:
                    $type = 'Catchable';
                    break;
                default:
                    $type = 'Unknown Error';
                    break;
            }
        }
        self::$type = $type;
        throw new \ErrorException($message, 0, $severity, $filename, $lineno);
    }
}