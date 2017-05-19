<?php

spl_autoload_register(function ($name) {
    include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php';
});

new Debug\BittrDbug(\Debug\BittrDbug::PRETTIFY, 'yola', 10);

?>
    <!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Bittr</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="shortcut icon" href="favicon.ico">

    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,600,400italic,700' rel='stylesheet' type='text/css'>

    <link type="text/css" rel="stylesheet" href="http://localhost/new_Bittr/www/Assets/css/animate.css">
    <link type="text/css" rel="stylesheet" href="http://localhost/new_Bittr/www/Assets/css/icomoon.css">
    <link type="text/css" rel="stylesheet" href="http://localhost/new_Bittr/www/Assets/css/simple-line-icons.css">
    <link type="text/css" rel="stylesheet" href="http://localhost/new_Bittr/www/Assets/css/bootstrap.css">
    <link type="text/css" rel="stylesheet" href="http://localhost/new_Bittr/www/Assets/css/style.css">

</head><body>
<header role="banner" id="fh5co-header">
    <div class="container">
        <!-- <div class="row"> -->
        <nav class="navbar navbar-default">
            <div class="navbar-header">
                <!-- Mobile Toggle Menu Button -->
                <a href="#" class="js-fh5co-nav-toggle fh5co-nav-toggle" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"><i></i></a>
                <a class="navbar-brand" href="index.html">Bittr</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="active"><a href="#" data-nav-section="home"><span>Home</span></a></li>
                    <li><a href="#" data-nav-section="work"><span>Work</span></a></li>
                    <li><a href="#" data-nav-section="testimonials"><span>Testimonials</span></a></li>
                    <li><a href="#" data-nav-section="services"><span>Services</span></a></li>
                    <li><a href="#" data-nav-section="about"><span>About</span></a></li>
                    <li><a href="#" data-nav-section="contact"><span>Contact</span></a></li>
                </ul>
            </div>
        </nav>
        <!-- </div> -->
    </div>
</header><section id="fh5co-home" data-section="home" data-stellar-background-ratio="0.5">
    <div class="gradient"></div>
    <div class="container">
        <div class="text-wrap">
            <div class="text-inner">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2" style="text-shadow: 1px 1px 1px #666;">
                        <h1 class="to-animate bounceIn animated">Do something you love.</h1>
                        <h2 class="to-animate bounceIn animated">Another free HTML5 bootstrap template by <a href="http://freehtml5.co/" target="_blank" title="Free HTML5 Bootstrap Templates">FREEHTML5.co</a> released under <a href="http://creativecommons.org/licenses/by/3.0/" target="_blank">Creative Commons 3.0</a></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="slant"></div>
</section>

<section id="fh5co-intro" class="animated">
    <div class="container">
        <div class="row row-bottom-padded-lg">
            <div class="fh5co-block to-animate fadeInRight animated" style="background-image: url(http://localhost/new_Bittr/www/Assets/img/img_7.jpg);">
                <div class="overlay-darker"></div>
                <div class="overlay"></div>
                <div class="fh5co-text">
                    <div class="fh5co-intro-icon">
                    </div>
<?php
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





