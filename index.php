<?php

spl_autoload_register(function ($name) {
    include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php';
});

new Debug\BittrDbug(\Debug\BittrDbug::PRETTIFY, 'yola', 10);

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

function a()
{
    duplicateKey();
}

function b()
{
    a();
}

function c()
{
    b();
}

function d()
{
    c();
}

function e()
{
    d();
}

function f()
{
    e();
}

function i()
{
    f();
}

function j()
{
    i();
}

function k()
{
    j();
}

function l()
{
    k();
}

function m()
{
    l();
}

function n()
{
    m();
}

function o()
{
    n();
}

function p()
{
    o();
}

function q()
{
    p();
}

q();

var_dump(new Foo());
#duplicateKey();;
//\BittrEHandler\Modules\Dump::error();





