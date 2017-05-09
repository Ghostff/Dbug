<?php

namespace Debug;

class Init
{

    public static $chunk = 0;

    public static $time = 0;

    public static $theme = null;

    public static $path = null;

    public function __construct($type = null, string $theme_or_log_path = null, int $line_range = 20)
    {
        ob_start();

        if ( $type == null || is_string($type))
        {
            self::$path = $theme_or_log_path;
            if ($type == 'prettify')
            {
                self::$theme = $theme_or_log_path;
                if (Highlight::theme($theme_or_log_path, 'yola') == 1)
                {
                    self::$theme = 'yola';
                }
            }
            $type = ['Debug\Handlers\ExceptionHandler', $type];
        }

        self::$chunk = $line_range;

        self::$time = microtime(true);
        set_exception_handler($type);
        set_error_handler(['Debug\Handlers\ErrorHandler', 'handle']);

    }

}

