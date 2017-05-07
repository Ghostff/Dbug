<?php
/**
 * Created by PhpStorm.
 * User: Chrys
 * Date: 4/19/2017
 * Time: 1:15 PM
 */

namespace BittrEHandler\Modules\Handlers;


use BittrEHandler\Modules\Contents;
use BittrEHandler\Modules\Init;

class ExceptionHandler
{
    public static function prettify(\Throwable $e)
    {
        $type = ErrorHandler::$type;
        if ($type !== null)
        {
            ErrorHandler::$type = null;
        }
        else
        {
            $type = get_class($e);
        }
        echo sprintf(Contents::template(),
            'http://Debug/',
            Contents::top(),
            Contents::left($e->getFile(), $e->getLine(), $e->getCode(), $e->getTrace()),
            Contents::middle($type, $e->getMessage(), $e->getFile(), $e->getLine()),
            Contents::right()
        );
    }

    public static function fileLog(\Throwable $e)
    {
        $type = ErrorHandler::$type;
        if ($type !== null)
        {
            ErrorHandler::$type = null;
        }
        else
        {
            $type = get_class($e);
        }

        $template = '[%s] [%s] %s %s:%d [%s]' . PHP_EOL;
        $new_trace = '';

        $trace = $e->getTrace();

        $_trace = count($trace) - 1;
        for ($i = $_trace; $i >= 0; $i--)
        {
            $t = $trace[$i];
            if ( ! isset($t['file']))
            {
                continue;
            }

            if (isset($t['type']))
            {
                $peaces = explode('\\', $t['class']);
                $class = end($peaces);
                $function = $class . $t['type'] . $t['function'];
            }
            else
            {
                $function = $t['function'];
            }

            $new_trace .= '    [' . $function. '] ' . $t['file'] . ':' . $t['line'] . PHP_EOL;
        }

        $new_trace = PHP_EOL . $new_trace;
        $file = sprintf($template, date("d-m-Y H:i:s"), $type, $e->getMessage(), $e->getFile(), $e->getLine(), $new_trace);
        file_put_contents(Init::$path, $file, FILE_APPEND);
        ob_end_clean();
    }
}