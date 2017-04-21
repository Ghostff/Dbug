<?php
spl_autoload_register(function ($name) {
    include str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
    if ($name)
    {

    }
    /**
     * check this
     *
     */
});