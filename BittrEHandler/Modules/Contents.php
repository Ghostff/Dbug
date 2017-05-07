<?php

namespace BittrEHandler\Modules;

class Contents
{
    private static $contents = [];

    private static function chunk($file, $line)
    {
        $chunk_count = Init::$chunk;
        $codes = file($file);
        $_code = [];

        $up = ($line - 1) - 1;
        $down = ($line - 1) + 1;
        $highlight_line = 0;
        $start_number = 0;
        for ($i = 0; $i < $chunk_count; $i++)
        {
            if (isset($codes[$up]))
            {
                $highlight_line++;
                $start_number = $up;
                $_code[$up] = $codes[$up];
            }
            if (isset($codes[$down]))
            {
                $_code[$down] = $codes[$down];
            }
            $up--;
            $down++;
        }
        Highlight::showLineNumber(true, $start_number);
        Highlight::setHighlight($line, ['class' => 'highlighted'], true);
        $_code[$line-1] = $codes[$line-1];
        ksort($_code);

        return implode($_code);
    }

    public static function highlight($message)
    {
        return preg_replace('/(\'(.*?)\'|"(.*?)")/s', '<span class="char-string">$1</span>', $message);
    }

    public static function top()
    {

        $selected_theme = Init::$theme;
        $theme_file = __DIR__ . '/theme.json';
        $_theme = file_get_contents($theme_file);
        $theme = json_decode($_theme, true);
        $select = '';
        foreach ($theme as $names => $vals)
        {
            $select .= '<li><a href="?theme=' . $names . '">' . $names . '</a></li> <li role="separator" class="divider"></li>';
        }

        return '<div class="logo tops">
                    <span class="logo-img"></span>
                    <span class="theme">Theme: ' . $selected_theme . '</span>
                </div>
                <div class="hints tops">
                    <div class="type type-object">OBJECT</div>
                    <div class="type type-null">NULL</div>
                    <div class="type type-bool">BOOL</div>
                    <div class="type type-array">ARRAY</div>
                    <div class="type type-float">FLOAT</div>
                    <div class="type type-double">DOUBLE</div>
                    <div class="type type-string">STRING</div>
                    <div class="type type-integer">INTEGER</div>
                </div>
                ';
    }

    public static function left($file, $line, $code, $trace = [])
    {
        $file_name = basename($file);
        $file_path = rtrim($file, $file_name);

        $start = Init::$time;
        $traced = '';
        $memory = '';
        $_trace = count($trace) - 1;

        for ($i = 0; $i <= $_trace; $i++)
        {
            $traces = $trace[$i];
            if(isset($traces['class']))
            {
                if ( ! isset($traces['line']))
                {
                   continue;
                }

                $peaces = explode('\\', $traces['class']);
                $class = end($peaces);
                $namespace = str_replace('\\', ' <b>\</b> ', rtrim($traces['class'], $class));
                $type = $traces['type'];
                $function = $traces['function'];
                $file = $traces['file'];
                $line = $traces['line'];
            }
            else
            {
                $class = $traces['function'];
                $namespace = '$_GLOBAL';
                $type = '()';
                $file = $traces['file'];
                $line = $traces['line'];
                $function = '';
            }

            $traced .= '<div class="function loop-tog" data-id="proc-' . $i . '">
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">' . $class . '<b>' . $type . '</b>' . $function . '<i class="line">' . $line . '</i></span>
                                <span class="path">' . $namespace . '</span> 
                            </div>   
                        </div>';


            $micro_time = microtime(true) - $start;

            $memory .= '<div class="memory loop-tog" data-id="proc-' . $i . '">
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">' . memory_get_usage() . '</span>
                                <span class="path"> ' .$micro_time . '</span> 
                            </div>
                       </div>';

            $_code = self::chunk($file, $line);
            self::$contents[] = '<div class="code-view" id="proc-' . $i . '" style="display:none;">' . Highlight::render($_code) . '</div>';
        }


        return '<div class="content-nav">
                    <div class="top-tog active" id="location">Location</div> 
                    <div class="top-tog" id="function">Trace</div> 
                    <div class="top-tog" id="memory">Memory</div> 
                </div>
                <div class="content-body">
                    <div class="loops">
                        <div class="location loop-tog active" data-id="proc-main">
                            <div class="id loop-tog code">' . $code . '</div>
                            <div class="holder">
                                <span class="name">' . $file_name . '<i class="line">' . $line . '</i> </span>
                                <span class="path">' . $file_path . '</span>             
                            </div>   
                        </div>
                        <div class="location loop-tog active" data-id="proc-buffer">
                            <div class="holder">
                                <span class="name" style="padding-left: 0px;">Output Buffer</span>
                                <span class="path">Toggle contents sent to output buffer</span>             
                            </div>   
                        </div>' . $traced . $memory . '<div class="time loop-tog">
                       </div></div>
                </div> ';
    }

    public static function middle($type, $message, $file, $line)
    {
        $code = self::chunk($file, $line);
        $output = ob_get_clean();
        if ($output == '')
        {
            $output = '<h3 style="text-align: center;">No output sent to buffer</h3>';
        }

        $g = 'php ' . $type . ' ' . $message;
        $s = '[php] ' . $message;
        return '<div class="exception-type">
                    <span>' . $type . '</span>
                    <div class="action">
                        <span title="lookup error message in stackoveflow" url="http://stackoverflow.com/search?q=' . $s . '"><span class="caret"></span> stackoverflow</span>
                        <span title="lookup error message in google" url="https://www.google.com/search?q=' . $g . '"><span class="caret"></span> google</span>
                    </div>
                </div>
                <div class="exception-msg">' . self::highlight($message) . '</div>
                <div class="code-view" id="proc-main">' . Highlight::render($code) . '</div>
                <div class="browser-view" id="proc-buffer" style="overflow:auto">' . $output . '</div>' . implode(self::$contents);
        exit;
    }

    public static function right()
    {

        $globals = array(
            'Server' => isset($_SERVER) ? $_SERVER : array(),
            'Get' => isset($_GET) ? $_GET : array(),
            'Post' => isset($_POST) ? $_POST : array(),
            'Files' => isset($_FILES) ? $_FILES : array(),
            'Request' => isset($_REQUEST) ? $_REQUEST : array(),
            'Session' => isset($_SESSION) ? $_SESSION : array(),
            'Cookie' => isset($_COOKIE) ? $_COOKIE : array(),
            'Env' => isset($_ENV) ? $_ENV : array('https://edmondscommerce.github.io/php/php-custom-error-and-exception-handler-make-php-stricter.html')
        );

        $side = '';
        $count = 0;
        foreach ($globals as $names => $attributes)
        {
            $hide = ($count > 0) ? ' style="display:none;"' : '';
            $side .= '<div class="global"><div class="labeled"><span class="caret"></span> &nbsp;&nbsp; ' . $names . '</div><div class="content"' . $hide. '>' . PHP_EOL;
            foreach ($attributes as $key => $values)
            {
                $side .= '<div class="listed">
                            <span class="index">' . $key . '</span> :
                            <span class="value">' . Type::get($values) . '</span>
                        </div>';
            }
            $side .= '</div></div>';
            $count++;
        }
        return $side;
    }

    public static function template()
    {
        return '<!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta charset="utf-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <title>Bittr Debug</title>
                        <style>
                            @font-face{font-family:InconsolataRegular;src:url(%1$sAssets/fonts/InconsolataRegular.ttf)}
                            @font-face{font-family:InconsolataBold;src:url(%1$sAssets/fonts/InconsolataBold.ttf)}
                        </style>
                        <link href="%1$s/Assets/css/bootstrap.css" rel="stylesheet">
                        <link href="%s/Assets/css/' . Init::$theme . '.css" rel="stylesheet">
                        <!--[if lt IE 9]>
                          <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
                          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
                        <![endif]-->
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
                    </body>
               </html>';
    }

}