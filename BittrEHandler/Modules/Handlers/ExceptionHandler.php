<?php
/**
 * Created by PhpStorm.
 * User: Chrys
 * Date: 4/19/2017
 * Time: 1:15 PM
 */

namespace BittrEHandler\Modules\Handlers;


use BittrEHandler\Modules\Contents;
use BittrEHandler\Modules\Dump;

class ExceptionHandler
{
    public static function Handle(\Exception $e)
    {

        #var_dump($e);exit;
        echo sprintf(Contents::template(),
            'http://localhost/Debug/',
            Contents::top(),
            Contents::left($e->getFile(), $e->getLine(), $e->getCode(), $e->getTrace()),
            Contents::middle(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
            Contents::right()
        );
    }
}