<?php

namespace BittrEHandler\Modules;

class Init
{

    public static $config = [];

    public static $time = 0;

    public static $theme = null;

    public function __construct()
    {
        if (isset($_GET['theme']))
        {
            file_put_contents(__DIR__ . '/theme', $_GET['theme']);
            header('location: /index.php');
            exit;
        }

        $theme = file_get_contents(__DIR__ . '/theme');
        self::$theme = $theme;
        if (Highlight::theme($theme, 'yola') == 1)
        {
            self::$theme = 'yola';
        }
        self::$config['chunk'] = 30;
        self::$time = microtime(true);
        #set_error_handler(array($this, 'exceptionHandler'));
        set_exception_handler(array('BittrEHandler\Modules\Handlers\ExceptionHandler', 'handle'));

    }

}

