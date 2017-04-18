<?php

namespace BittrEHandler\Modules;

class Init
{
    public function __construct()
    {
        echo sprintf($this->template(),
            'http://debug/',
            'dark',
            Contents::top(),
            Contents::left(),
            Contents::middle(),
            Contents::right(),
            Contents::bottom()
        );
    }

    public function template()
    {
        return '<!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="utf-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <title>Bittr Debug</title>
                        <link href="%1$s/Assets/css/bootstrap.css" rel="stylesheet">
                        <link href="%1$s/Assets/css/jquery.mCustomScrollbar.css" rel="stylesheet">
                        <link href="%s/Assets/css/%s.css" rel="stylesheet">
                    </head>
                    <body>
                    
                        <div class="header">%s</div>
                        <div class="container-fluid">
                            <div class="row contents">
                                <div class="col-md-3 attr left">%s</div>
                                <div class="col-md-6 attr middle">%s</div>
                                <div class="col-md-3 attr right">%s</div>
                            </div>
                        </div>

                        <script src="%1$s/Assets/js/jquery.min.js"></script>
                        <script src="%1$s/Assets/js/bootstrap.min.js"></script>
                        <script src="%1$s/Assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
                        <script src="%1$s/Assets/js/custom.js"></script>
                    </body>
               </html>';
    }
}

