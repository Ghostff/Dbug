<?php

namespace BittrEHandler\Modules;

class Init
{

    public static $chunk = 0;

    public static $time = 0;

    public static $theme = null;

    public static $path = null;

    public function __construct($type = null, string $theme_or_log_path = null, int $line_range = 20)
    {
        if ( $type == null || is_string($type))
        {
            self::$path = $theme_or_log_path;
            if ($type == 'prettify')
            {
                if ( $theme_or_log_path == null)
                {
                    if (isset($_GET['theme']))
                    {
                        file_put_contents(__DIR__ . '/theme', $_GET['theme']);
                        header('location: /index.php');
                        exit;
                    }
                    $theme_or_log_path = file_get_contents(__DIR__ . '/theme');

                }

                self::$theme = $theme_or_log_path;
                if (Highlight::theme($theme_or_log_path, 'yola') == 1)
                {
                    self::$theme = 'yola';
                }
            }
            $type = ['BittrEHandler\Modules\Handlers\ExceptionHandler', $type];
        }

        self::$chunk = $line_range;

        self::$time = microtime(true);
        set_exception_handler($type);
        set_error_handler(['BittrEHandler\Modules\Handlers\ErrorHandler', 'handle']);

    }

}

