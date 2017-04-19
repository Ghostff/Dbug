<?php
/**
 * Created by PhpStorm.
 * User: Chrys
 * Date: 4/17/2017
 * Time: 12:38 PM
 */

namespace BittrEHandler\Modules;


class Contents
{
    private static function chunk($file, $line)
    {
        $chunk_count = Init::$config['chunk'];
        $codes = file($file);
        $_code = array();

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
        Highlight::$highlight_line = $line;
        Highlight::$start_number = $start_number;
        $_code[$line-1] = $codes[$line-1];
        ksort($_code);

        return implode( $_code);
    }

    public static function highlight($message)
    {
        return preg_replace('/(\'(.*?)\'|"(.*?)")/s', '<span style="color:#ff7c88;">$1</span>', $message);
    }

    public static function top()
    {
        return '<div class="type" style="background:#6789f8";>NULL</div>
                <div class="type" style="background:#f8b93c;">BOOL</div>
                <div class="type" style="background:#6db679;">ARRAY</div>
                <div class="type" style="background:#9C6E25;">FLOAT</div>
                <div class="type" style="background:#a66b47;">DOUBLE</div>
                <div class="type" style="background:#ff9999;">STRING</div>
                <div class="type" style="background:#000000;">OBJECT</div>
                <div class="type" style="background:#1BAABB;">INTEGER</div>';
    }

    public static function left($file, $line, $code, $trace = array())
    {
        $file_name = basename($file);
        $file_path = rtrim($file, $file_name);

        $start = Init::$time;
        $traced = '';
        $time = '';
        $memory = '';
        $_trace = count($trace) - 1;
        for ($i = $_trace; $i >= 0; $i--)
        {
            $traces = $trace[$i];
            $class = basename($traces['class']);
            $namespace = str_replace('\\', ' <b>\</b> ', rtrim($traces['class'], $class));
            $traced .= '<div class="function loop-tog">
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">' . $class . ' <b>' . $traces['type'] . '</b>' . $traces['function'] . '<i class="line">' . $traces['line'] . '</i></span>
                                <span class="path">' . $namespace . '</span> 
                            </div>   
                        </div>';

            $micro_time = microtime(true) - $start;

            $hours = (int)($micro_time/60/60);
            $minutes = (int)($micro_time/60)-$hours*60;
            $seconds = (int)$micro_time-$hours*60*60-$minutes*60;

            $time .= ' <div class="time loop-tog">
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">' . $micro_time . '</span>
                                <span class="path">' .$hours . ':' . $minutes . ':' . $seconds . '</span> 
                            </div>
                       </div>';

            $memory .= '<div class="memory loop-tog">
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">' . memory_get_usage() . '</span>
                                <span class="path"> ' .$hours . ':' . $minutes . ':' . $seconds . '</span> 
                            </div>
                       </div>';
        }

        return '<div class="content-nav">
                    <div class="top-tog active" id="location">Location</div> 
                    <div class="top-tog" id="function">Trace</div> 
                    <div class="top-tog" id="time">Time</div> 
                    <div class="top-tog" id="memory">Memory</div> 
                </div>
                <div class="content-body">
                    <div class="loops">
                        <div class="location loop-tog active">
                            <div class="id loop-tog code">' . $code . '</div>
                            <div class="holder">
                                <span class="name">' . $file_name . '<i class="line">' . $line . '</i> </span>
                                <span class="path">' . $file_path . '</span>             
                            </div>   
                        </div>' . $traced . $time . $memory . '</div>
                </div> ';
    }

    public static function middle($type, $message, $file, $line)
    {
        Highlight::numberLines();

        $code = self::chunk($file, $line);

        return '<div class="exception-type"><span>' . $type . '</span></div>
                <div class="exception-msg">' . self::highlight($message) . '</div>
                <div class="code-view">' . Highlight::render($code) . '</div>';
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