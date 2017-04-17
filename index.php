<?php 
spl_autoload_register(function ($name) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
});


new BittrEHandler\Modules\Init();


#throw new \Exception("Error Processing Request", 1);
?>