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
new BittrEHandler\Modules\Init(


);

class Foo
{
    private $string = 'string';
    protected $int = 10;
    public $array = [
        'foo'   => 'bar'
    ];
    protected static $bool = false;
}
$_POST = ['foo' => 22, 'bar' => new Foo()];

new \BittrEHandler\Modules\Dump('Hey man');



