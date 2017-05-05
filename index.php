<?php

ob_start();

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
echo 111;
new BittrEHandler\Modules\Init('prettify', 'default', 20);


class Foo
{
    private $string = 'string';
    protected $int = 20;
    public $array = [
        'foo'   => 'bar'
    ];
    protected static $bool = false;
}
$_POST = ['foo' => 22, 'bar' => new Foo()];

function renderError()
{
    trigger_error("Cannot divide by zero", E_USER_ERROR);
}
function duplicateKey()
{
    renderError();
}

#duplicateKey();
\BittrEHandler\Modules\Dump::error();






