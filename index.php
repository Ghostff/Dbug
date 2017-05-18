<?php



spl_autoload_register(function ($name) {
    include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php';
    #include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php';

});

new Debug\BittrDbug(\Debug\BittrDbug::PRETTIFY, 'default', 10);

echo 111;
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

duplicateKey();;
//\BittrEHandler\Modules\Dump::error();





