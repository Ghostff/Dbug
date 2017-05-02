<?php

namespace BittrEHandler\Modules;

class Init
{

    public static $config = [];

    public static $time = 0;

    public function __construct()
    {

        self::$config['chunk'] = 30;
        self::$time = microtime(true);
        #set_error_handler(array($this, 'exceptionHandler'));
        set_exception_handler(array('BittrEHandler\Modules\Handlers\ExceptionHandler', 'handle'));

    }

}

