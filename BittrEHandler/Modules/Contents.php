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
    public static function highlight($message)
    {
        return $message;
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

    public static function left()
    {
        return '<div class="content-nav">
                    <div class="top-tog">function</div> 
                    <div class="top-tog">time</div> 
                    <div class="top-tog">memory</div> 
                    <div class="top-tog">location</div> 
                </div>
                <div class="content-body"></div> ';
    }


    public static function middle()
    {
        Highlight::numberLines();
        Highlight::$highlight_line = 10;
        return '<div class="exception-type"><span>RuntimeException</span></div>
                <div class="exception-msg">' . self::highlight('Error Processing Request') . '</div>
                <div class="code-view">' . Highlight::render('TextTable.php', 1, 0) . '</div>';
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

    public static function bottom()
    {

    }
}