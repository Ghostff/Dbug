<?php

namespace BittrEHandler\Modules;

class Init
{

    public static $config = [];

    public static $time = 0;

    public static $theme = null;

    public function __construct()
    {

        #file_put_contents(__DIR__ . '/theme', 'yola');
        $theme = file_get_contents(__DIR__ . '/theme');
        self::$theme = $theme;
        Highlight::theme($theme);
        self::$config['chunk'] = 30;
        self::$time = microtime(true);
        #set_error_handler(array($this, 'exceptionHandler'));
        set_exception_handler(array('BittrEHandler\Modules\Handlers\ExceptionHandler', 'handle'));

    }

}

