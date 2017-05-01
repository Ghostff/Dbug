<?php


spl_autoload_register(function ($name) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
    if ($name)
    {
        if (3 == 13)
        {
            new \BittrEHandler\Modules\Dump('working on test');
        }
    }
    /**
     * check this
     *
     */
});
new BittrEHandler\Modules\Init();
new \BittrEHandler\Modules\Dump('Hey man');



