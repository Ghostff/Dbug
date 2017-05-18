<?php



spl_autoload_register(function ($name) {
    include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php';
    #include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php';

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
    <title>Union &mdash; 100% Free Fully Responsive HTML5 Template by FREEHTML5.co</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Free HTML5 Template by FREEHTML5.CO" />
    <meta name="keywords" content="free html5, free template, free bootstrap, html5, css3, mobile first, responsive" />
    <meta name="author" content="FREEHTML5.CO" />

    <!--
      //////////////////////////////////////////////////////

      FREE HTML5 TEMPLATE
      DESIGNED & DEVELOPED by FREEHTML5.CO

      Website: 		http://freehtml5.co/
      Email: 			info@freehtml5.co
      Twitter: 		http://twitter.com/fh5co
      Facebook: 		https://www.facebook.com/fh5co

      //////////////////////////////////////////////////////
       -->

    <!-- Facebook and Twitter integration -->
    <meta property="og:title" content=""/>
    <meta property="og:image" content=""/>
    <meta property="og:url" content=""/>
    <meta property="og:site_name" content=""/>
    <meta property="og:description" content=""/>
    <meta name="twitter:title" content="" />
    <meta name="twitter:image" content="" />
    <meta name="twitter:url" content="" />
    <meta name="twitter:card" content="" />

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <link rel="shortcut icon" href="favicon.ico">

    <link href='https://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>

    <!-- Animate.css -->
    <link rel="stylesheet" type="text/css" href="http://localhost/NasFound/Assets/Home/css/animate.css">
    <link rel="stylesheet" type="text/css" href="http://localhost/NasFound/Assets/Home/css/icomoon.css">
    <link rel="stylesheet" type="text/css" href="http://localhost/NasFound/Assets/Home/css/simple-line-icons.css">
    <link rel="stylesheet" type="text/css" href="http://localhost/NasFound/Assets/Home/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="http://localhost/NasFound/Assets/Home/css/owl.carousel.min.css">
    <link rel="stylesheet" type="text/css" href="http://localhost/NasFound/Assets/Home/css/owl.theme.default.min.css">
    <link rel="stylesheet" type="text/css" href="http://localhost/NasFound/Assets/Home/css/style.css">

</head><body>
<header role="banner" id="fh5co-header">
    <div class="fluid-container">
        <nav class="navbar navbar-default">
            <div class="navbar-header" style="width: 200px">
                <!-- Mobile Toggle Menu Button -->
                <a href="#" class="js-fh5co-nav-toggle fh5co-nav-toggle" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"><i></i></a>
                <a class="site_title" href="index.html"><i class="fa fa-paw"></i><span>Pomzify</span></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="active"><a href="#" data-nav-section="home"><span>Home</span></a></li>
                    <li><a href="#" data-nav-section="explore"><span>Explore</span></a></li>
                    <li><a href="#" data-nav-section="testimony"><span>Testimony</span></a></li>
                    <li><a href="#" data-nav-section="pricing"><span>Pricing</span></a></li>
                    <li class="call-to-action"><a href="/login"><span>Sign up</span></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header><section id="fh5co-home" data-section="home" data-stellar-background-ratio="0.5">
    <div class="container">
        <div class="text-wrap top_info">
            <div class="text-inner">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <h1 class="to-animate">Union One Page template for everyone</h1>
                        <h2 class="to-animate">100% Free HTML5 Template. Licensed under <a href="http://creativecommons.org/licenses/by/3.0/" target="_blank">Creative Commons Attribution 3.0.</a> <br> Crafted with love by <a href="http://freehtml5.co/" target="_blank" title="Free HTML5 Bootstrap Templates" class="fh5co-link">FREEHTML5.co</a></h2>
                        <div class="call-to-action">
                            <a href="#" class="demo to-animate">Login</a>
                            <a href="#" class="download to-animate">Register</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="fh5co-explore" data-section="explore">
    <div class="container">
        <div class="row">
            <div class="col-md-12 section-heading text-center">
                <h2 class="to-animate">Explore Our Products</h2>
                <div class="row">
                    <div class="col-md-8 col-md-offset-2 subtext to-animate">
                        <h3>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fh5co-explore">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-push-5 to-animate-2">
                    <img src="http://localhost/NasFound/Assets/Home/img/work_1.png"  alt="work" class="img-responsive">
                </div>
                <div class="col-md-4 col-md-pull-8 to-animate-2">
                    <div class="mt">
                        <h3>Real Project For Real Solutions</h3>
                        <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean. </p>
                        <ul class="list-nav">
                            <li><i class="icon-check2"></i>Far far away, behind the word</li>
                            <li><i class="icon-check2"></i>There live the blind texts</li>
                            <li><i class="icon-check2"></i>Separated they live in bookmarksgrove</li>
                            <li><i class="icon-check2"></i>Semantics a large language ocean</li>
                            <li><i class="icon-check2"></i>A small river named Duden</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="fh5co-explore fh5co-explore-bg-color">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-pull-1 to-animate-3">
                    <img src="http://localhost/NasFound/Assets/Home/img/work_1.png"  alt="work" class="img-responsive">
                </div>
                <div class="col-md-4 to-animate-3">
                    <div class="mt">
                        <div>
                            <h4><i class="icon-people"></i>Real Project For Real Solutions</h4>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                        </div>
                        <div>
                            <h4><i class="icon-video2"></i>Real Project For Real Solutions</h4>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                        </div>
                        <div>
                            <h4><i class="icon-shield"></i>Real Project For Real Solutions</h4>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="fh5co-testimony" data-section="testimony">
    <div class="container">
        <div class="row">
            <div class="col-md-12 to-animate">
                <div class="wrap-testimony">
                    <div class="owl-carousel-fullwidth">
                        <div class="item">
                            <div class="testimony-slide active text-center">
                                <figure>
                                    <img src="http://localhost/NasFound/Assets/Home/img/person2.jpg"  alt="user">
                                </figure>
                                <blockquote>
                                    <p>"Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean."</p>
                                </blockquote>
                                <span>John Doe, via <a href="#" class="twitter">Twitter</a></span>
                            </div>
                        </div>
                        <div class="item">
                            <div class="testimony-slide active text-center">
                                <figure>
                                    <img src="http://localhost/NasFound/Assets/Home/img/person3.jpg"  alt="user">
                                </figure>
                                <blockquote>
                                    <p>"Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean."</p>
                                </blockquote>
                                <span>John Doe, via <a href="#" class="twitter">Twitter</a></span>
                            </div>
                        </div>
                        <div class="item">
                            <div class="testimony-slide active text-center">
                                <figure>
                                    <img src="http://localhost/NasFound/Assets/Home/img/person2.jpg"  alt="user">
                                </figure>
                                <blockquote>
                                    <p>"Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large language ocean."</p>
                                </blockquote>
                                <span>John Doe, via <a href="#" class="twitter">Twitter</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="getting-started getting-started-1">
    <div class="container">
        <div class="row">
            <div class="col-md-6 to-animate">
                <h3>Getting Started</h3>
                <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
            </div>
            <div class="col-md-6 to-animate-2">
                <div class="call-to-action text-right">
                    <a href="#" class="sign-up">Sign Up For Free</a>
                </div>
            </div>
        </div>
    </div>
</div>

<section id="fh5co-pricing" data-section="pricing">
    <div class="fh5co-pricing">
        <div class="container">
            <div class="row">
                <div class="col-md-12 section-heading text-center">
                    <h2 class="to-animate">Plans Built For Every One</h2>
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2 subtext">
                            <h3 class="to-animate">Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove. </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="pricing">
                    <div class="col-md-3">
                        <div class="price-box to-animate-2">
                            <h2 class="pricing-plan">Starter</h2>
                            <div class="price"><sup class="currency">$</sup>9<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="price-box to-animate-2">
                            <h2 class="pricing-plan">Basic</h2>
                            <div class="price"><sup class="currency">$</sup>27<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="price-box to-animate-2 popular">
                            <h2 class="pricing-plan pricing-plan-offer">Pro <span>Best Offer</span></h2>
                            <div class="price"><sup class="currency">$</sup>74<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="price-box to-animate-2">
                            <h2 class="pricing-plan">Unlimited</h2>
                            <div class="price"><sup class="currency">$</sup>140<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>
                </div>
            </div>



        </div>
        <div class="container">
            <div class="row">
                <div class="pricing">
                    <div class="col-md-3">
                        <div class="price-box to-animate-2">
                            <h2 class="pricing-plan">Starter</h2>
                            <div class="price"><sup class="currency">$</sup>9<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="price-box to-animate-2">
                            <h2 class="pricing-plan">Basic</h2>
                            <div class="price"><sup class="currency">$</sup>27<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="price-box to-animate-2 popular">
                            <h2 class="pricing-plan pricing-plan-offer">Pro <span>Best Offer</span></h2>
                            <div class="price"><sup class="currency">$</sup>74<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="price-box to-animate-2">
                            <h2 class="pricing-plan">Unlimited</h2>
                            <div class="price"><sup class="currency">$</sup>140<small>/month</small></div>
                            <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. </p>
                            <a href="#" class="btn btn-select-plan btn-sm">Select Plan</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-md-offset-3 to-animate">
                    <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. <a href="#">Learn More</a></p>
                </div>
            </div>

        </div>
    </div>
</section>




<section id="fh5co-trusted" data-section="trusted">
    <div class="fh5co-trusted">
        <div class="container">
            <div class="row">
                <div class="col-md-12 section-heading text-center">
                    <h2 class="to-animate">Trusted By</h2>
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2 subtext">
                            <h3 class="to-animate">We�re trusted by these popular companies</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 col-sm-3 col-xs-6 col-sm-offset-0 col-md-offset-1">
                    <div class="partner-logo to-animate-2">
                        <img src="http://localhost/NasFound/Assets/Home/img/logo1.png"  alt="Partners" class="img-responsive">
                    </div>
                </div>
                <div class="col-md-2 col-sm-3 col-xs-6">
                    <div class="partner-logo to-animate-2">
                        <img src="http://localhost/NasFound/Assets/Home/img/logo2.png"  alt="Partners" class="img-responsive">
                    </div>
                </div>
                <div class="col-md-2 col-sm-3 col-xs-6">
                    <div class="partner-logo to-animate-2">
                        <img src="http://localhost/NasFound/Assets/Home/img/logo3.png"  alt="Partners" class="img-responsive">
                    </div>
                </div>
                <div class="col-md-2 col-sm-3 col-xs-6">
                    <div class="partner-logo to-animate-2">
                        <img src="http://localhost/NasFound/Assets/Home/img/logo4.png"  alt="Partners" class="img-responsive">
                    </div>
                </div>
                <div class="col-md-2 col-sm-12 col-xs-12">
                    <div class="partner-logo to-animate-2">
                        <img src="http://localhost/NasFound/Assets/Home/img/logo5.png"  alt="Partners" class="img-responsive">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div id="fh5co-footer" role="contentinfo">
    <div class="container">
        <div class="row">
            <div class="col-md-4 to-animate">
                <h3 class="section-title">About Us</h3>
                <p>Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics.</p>
                <p class="copy-right">&copy; 2015 Union Free Template. <br>All Rights Reserved. <br>
                    Designed by <a href="http://freehtml5.co/" target="_blank">FREEHTML5.co</a>
                    Demo Images: <a href="http://unsplash.com/" target="_blank">Unsplash</a> &amp; Dribbble Image by <a href="https://dribbble.com/tibi_neamu" target="_blank">Tiberiu</a>
                </p>
            </div>

            <div class="col-md-4 to-animate">
                <h3 class="section-title">Our Address</h3>
                <ul class="contact-info">
                    <li><i class="icon-map-marker"></i>198 West 21th Street, Suite 721 New York NY 10016</li>
                    <li><i class="icon-phone"></i>+ 1235 2355 98</li>
                    <li><i class="icon-envelope"></i><a href="#">info@yoursite.com</a></li>
                    <li><i class="icon-globe2"></i><a href="#">www.yoursite.com</a></li>
                </ul>
                <h3 class="section-title">Connect with Us</h3>
                <ul class="social-media">
                    <li><a href="#" class="facebook"><i class="icon-facebook"></i></a></li>
                    <li><a href="#" class="twitter"><i class="icon-twitter"></i></a></li>
                    <li><a href="#" class="dribbble"><i class="icon-dribbble"></i></a></li>
                    <li><a href="#" class="github"><i class="icon-github-alt"></i></a></li>
                </ul>
            </div>
            <div class="col-md-4 to-animate">
                <h3 class="section-title">Drop us a line</h3>
                <form class="contact-form">
                    <div class="form-group">
                        <label for="name" class="sr-only">Name</label>
                        <input type="name" class="form-control" id="name" placeholder="Name">
                    </div>
                    <div class="form-group">
                        <label for="email" class="sr-only">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Email">
                    </div>
                    <div class="form-group">
                        <label for="message" class="sr-only">Message</label>
                        <textarea class="form-control" id="message" rows="7" placeholder="Message"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" id="btn-submit" class="btn btn-send-message btn-md" value="Send Message">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div><script src="http://localhost/NasFound/Assets/Home/js/modernizr-2.6.2.min.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/respond.min.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/jquery.min.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/jquery.easing.1.3.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/bootstrap.min.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/jquery.waypoints.min.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/jquery.stellar.min.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/owl.carousel.min.js" ></script>
<script src="http://localhost/NasFound/Assets/Home/js/main.js" ></script>
</body>
</html>



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

duplicateKey();;
//\BittrEHandler\Modules\Dump::error();





