<?php

namespace Debug;

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
                if ( ! isset($traces['line']))
                {
                    continue;
                }
                $class = $traces['function'];
                $namespace = '$_GLOBAL';
                $type = '()';
                $file = $traces['file'] ?? '';
                $line = $traces['line'] ?? '';
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

            $_code = rtrim(self::chunk($file, $line));

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
        $code = rtrim(self::chunk($file, $line));
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
        return '<div class="header">%s</div>
        <div class="container-fluid">
            <div class="row contents">
                <div class="col-md-3 attr left">%s</div>
                <div class="col-md-6 attr middle">%s</div>
                <div class="col-md-3 attr right">%s</div>
            </div>
        </div>';
    }

    public static function html(string $content)
    {
        $DIRS = DIRECTORY_SEPARATOR;
        $theme_file = __DIR__ . $DIRS . 'Styles' . $DIRS;
        $theme = file_get_contents($theme_file . Init::$theme . '.css');
        $image = base64_encode(file_get_contents($theme_file . Init::$theme . '.png'));
        $font_reg = base64_encode(file_get_contents($theme_file . $DIRS . 'fonts' . $DIRS . 'InconsolataRegular.ttf'));
        $font_bld = base64_encode(file_get_contents($theme_file . $DIRS . 'fonts' . $DIRS . 'InconsolataBold.ttf'));

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bittr Debug</title>
        <style>
            @font-face{font-family:InconsolataRegular;src:url(data:font/truetype;charset=utf-8;base64,' . $font_reg . ') format("truetype");}
            @font-face{font-family:InconsolataBold;src:url(data:font/truetype;charset=utf-8;base64,' . $font_bld . ') format("truetype");}
            .contents .middle,body{overflow:hidden}.contents .right .global,.left .content-body .loops .holder:hover,.left .content-nav .top-tog{cursor:pointer}.contents .listed:last-child,.contents .right .global:last-child{border-bottom:none}html{font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-size:10px;-webkit-tap-highlight-color:transparent}body{margin:0;font-family:InconsolataBold!important;font-weight:500;font-style:normal;font-size:14px;line-height:1.42857143;color:#333;background-color:#fff}.container,.container-fluid{margin-right:auto;margin-left:auto;padding-right:15px;padding-left:15px}article,aside,details,figcaption,figure,footer,header,hgroup,main,menu,nav,section,summary{display:block}.caret,.header .hints .type,.left .content-nav .top-tog{display:inline-block}*,:after,:before{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}button,input,select,textarea{font-family:inherit;font-size:inherit;line-height:inherit}a{color:#337ab7;text-decoration:none}a:focus,a:hover{color:#23527c;text-decoration:underline}a:focus{outline:-webkit-focus-ring-color auto 5px;outline-offset:-2px}@media (min-width:768px){.container{width:750px}}@media (min-width:992px){.container{width:970px}}@media (min-width:1200px){.container{width:1170px}}.row{margin-right:-15px;margin-left:-15px}.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9,.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9,.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9,.col-xs-1,.col-xs-10,.col-xs-11,.col-xs-12,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-5,.col-xs-6,.col-xs-7,.col-xs-8,.col-xs-9{position:relative;min-height:1px;padding-right:15px;padding-left:15px}.col-xs-1,.col-xs-10,.col-xs-11,.col-xs-12,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-5,.col-xs-6,.col-xs-7,.col-xs-8,.col-xs-9{float:left}.col-xs-12{width:100%}.col-xs-11{width:91.66666667%}.col-xs-10{width:83.33333333%}.col-xs-9{width:75%}.col-xs-8{width:66.66666667%}.col-xs-7{width:58.33333333%}.col-xs-6{width:50%}.col-xs-5{width:41.66666667%}.col-xs-4{width:33.33333333%}.col-xs-3{width:25%}.col-xs-2{width:16.66666667%}.col-xs-1{width:8.33333333%}.col-xs-pull-12{right:100%}.col-xs-pull-11{right:91.66666667%}.col-xs-pull-10{right:83.33333333%}.col-xs-pull-9{right:75%}.col-xs-pull-8{right:66.66666667%}.col-xs-pull-7{right:58.33333333%}.col-xs-pull-6{right:50%}.col-xs-pull-5{right:41.66666667%}.col-xs-pull-4{right:33.33333333%}.col-xs-pull-3{right:25%}.col-xs-pull-2{right:16.66666667%}.col-xs-pull-1{right:8.33333333%}.col-xs-pull-0{right:auto}.col-xs-push-12{left:100%}.col-xs-push-11{left:91.66666667%}.col-xs-push-10{left:83.33333333%}.col-xs-push-9{left:75%}.col-xs-push-8{left:66.66666667%}.col-xs-push-7{left:58.33333333%}.col-xs-push-6{left:50%}.col-xs-push-5{left:41.66666667%}.col-xs-push-4{left:33.33333333%}.col-xs-push-3{left:25%}.col-xs-push-2{left:16.66666667%}.col-xs-push-1{left:8.33333333%}.col-xs-push-0{left:auto}.col-xs-offset-12{margin-left:100%}.col-xs-offset-11{margin-left:91.66666667%}.col-xs-offset-10{margin-left:83.33333333%}.col-xs-offset-9{margin-left:75%}.col-xs-offset-8{margin-left:66.66666667%}.col-xs-offset-7{margin-left:58.33333333%}.col-xs-offset-6{margin-left:50%}.col-xs-offset-5{margin-left:41.66666667%}.col-xs-offset-4{margin-left:33.33333333%}.col-xs-offset-3{margin-left:25%}.col-xs-offset-2{margin-left:16.66666667%}.col-xs-offset-1{margin-left:8.33333333%}.col-xs-offset-0{margin-left:0}@media (min-width:768px){.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9{float:left}.col-sm-12{width:100%}.col-sm-11{width:91.66666667%}.col-sm-10{width:83.33333333%}.col-sm-9{width:75%}.col-sm-8{width:66.66666667%}.col-sm-7{width:58.33333333%}.col-sm-6{width:50%}.col-sm-5{width:41.66666667%}.col-sm-4{width:33.33333333%}.col-sm-3{width:25%}.col-sm-2{width:16.66666667%}.col-sm-1{width:8.33333333%}.col-sm-pull-12{right:100%}.col-sm-pull-11{right:91.66666667%}.col-sm-pull-10{right:83.33333333%}.col-sm-pull-9{right:75%}.col-sm-pull-8{right:66.66666667%}.col-sm-pull-7{right:58.33333333%}.col-sm-pull-6{right:50%}.col-sm-pull-5{right:41.66666667%}.col-sm-pull-4{right:33.33333333%}.col-sm-pull-3{right:25%}.col-sm-pull-2{right:16.66666667%}.col-sm-pull-1{right:8.33333333%}.col-sm-pull-0{right:auto}.col-sm-push-12{left:100%}.col-sm-push-11{left:91.66666667%}.col-sm-push-10{left:83.33333333%}.col-sm-push-9{left:75%}.col-sm-push-8{left:66.66666667%}.col-sm-push-7{left:58.33333333%}.col-sm-push-6{left:50%}.col-sm-push-5{left:41.66666667%}.col-sm-push-4{left:33.33333333%}.col-sm-push-3{left:25%}.col-sm-push-2{left:16.66666667%}.col-sm-push-1{left:8.33333333%}.col-sm-push-0{left:auto}.col-sm-offset-12{margin-left:100%}.col-sm-offset-11{margin-left:91.66666667%}.col-sm-offset-10{margin-left:83.33333333%}.col-sm-offset-9{margin-left:75%}.col-sm-offset-8{margin-left:66.66666667%}.col-sm-offset-7{margin-left:58.33333333%}.col-sm-offset-6{margin-left:50%}.col-sm-offset-5{margin-left:41.66666667%}.col-sm-offset-4{margin-left:33.33333333%}.col-sm-offset-3{margin-left:25%}.col-sm-offset-2{margin-left:16.66666667%}.col-sm-offset-1{margin-left:8.33333333%}.col-sm-offset-0{margin-left:0}}@media (min-width:992px){.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9{float:left}.col-md-12{width:100%}.col-md-11{width:91.66666667%}.col-md-10{width:83.33333333%}.col-md-9{width:75%}.col-md-8{width:66.66666667%}.col-md-7{width:58.33333333%}.col-md-6{width:50%}.col-md-5{width:41.66666667%}.col-md-4{width:33.33333333%}.col-md-3{width:25%}.col-md-2{width:16.66666667%}.col-md-1{width:8.33333333%}.col-md-pull-12{right:100%}.col-md-pull-11{right:91.66666667%}.col-md-pull-10{right:83.33333333%}.col-md-pull-9{right:75%}.col-md-pull-8{right:66.66666667%}.col-md-pull-7{right:58.33333333%}.col-md-pull-6{right:50%}.col-md-pull-5{right:41.66666667%}.col-md-pull-4{right:33.33333333%}.col-md-pull-3{right:25%}.col-md-pull-2{right:16.66666667%}.col-md-pull-1{right:8.33333333%}.col-md-pull-0{right:auto}.col-md-push-12{left:100%}.col-md-push-11{left:91.66666667%}.col-md-push-10{left:83.33333333%}.col-md-push-9{left:75%}.col-md-push-8{left:66.66666667%}.col-md-push-7{left:58.33333333%}.col-md-push-6{left:50%}.col-md-push-5{left:41.66666667%}.col-md-push-4{left:33.33333333%}.col-md-push-3{left:25%}.col-md-push-2{left:16.66666667%}.col-md-push-1{left:8.33333333%}.col-md-push-0{left:auto}.col-md-offset-12{margin-left:100%}.col-md-offset-11{margin-left:91.66666667%}.col-md-offset-10{margin-left:83.33333333%}.col-md-offset-9{margin-left:75%}.col-md-offset-8{margin-left:66.66666667%}.col-md-offset-7{margin-left:58.33333333%}.col-md-offset-6{margin-left:50%}.col-md-offset-5{margin-left:41.66666667%}.col-md-offset-4{margin-left:33.33333333%}.col-md-offset-3{margin-left:25%}.col-md-offset-2{margin-left:16.66666667%}.col-md-offset-1{margin-left:8.33333333%}.col-md-offset-0{margin-left:0}}@media (min-width:1200px){.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9{float:left}.col-lg-12{width:100%}.col-lg-11{width:91.66666667%}.col-lg-10{width:83.33333333%}.col-lg-9{width:75%}.col-lg-8{width:66.66666667%}.col-lg-7{width:58.33333333%}.col-lg-6{width:50%}.col-lg-5{width:41.66666667%}.col-lg-4{width:33.33333333%}.col-lg-3{width:25%}.col-lg-2{width:16.66666667%}.col-lg-1{width:8.33333333%}.col-lg-pull-12{right:100%}.col-lg-pull-11{right:91.66666667%}.col-lg-pull-10{right:83.33333333%}.col-lg-pull-9{right:75%}.col-lg-pull-8{right:66.66666667%}.col-lg-pull-7{right:58.33333333%}.col-lg-pull-6{right:50%}.col-lg-pull-5{right:41.66666667%}.col-lg-pull-4{right:33.33333333%}.col-lg-pull-3{right:25%}.col-lg-pull-2{right:16.66666667%}.col-lg-pull-1{right:8.33333333%}.col-lg-pull-0{right:auto}.col-lg-push-12{left:100%}.col-lg-push-11{left:91.66666667%}.col-lg-push-10{left:83.33333333%}.col-lg-push-9{left:75%}.col-lg-push-8{left:66.66666667%}.col-lg-push-7{left:58.33333333%}.col-lg-push-6{left:50%}.col-lg-push-5{left:41.66666667%}.col-lg-push-4{left:33.33333333%}.col-lg-push-3{left:25%}.col-lg-push-2{left:16.66666667%}.col-lg-push-1{left:8.33333333%}.col-lg-push-0{left:auto}.col-lg-offset-12{margin-left:100%}.col-lg-offset-11{margin-left:91.66666667%}.col-lg-offset-10{margin-left:83.33333333%}.col-lg-offset-9{margin-left:75%}.col-lg-offset-8{margin-left:66.66666667%}.col-lg-offset-7{margin-left:58.33333333%}.col-lg-offset-6{margin-left:50%}.col-lg-offset-5{margin-left:41.66666667%}.col-lg-offset-4{margin-left:33.33333333%}.col-lg-offset-3{margin-left:25%}.col-lg-offset-2{margin-left:16.66666667%}.col-lg-offset-1{margin-left:8.33333333%}.col-lg-offset-0{margin-left:0}}.caret{width:0;height:0;margin-left:2px;vertical-align:middle;border-top:4px dashed;border-top:4px solid\9;border-right:4px solid transparent;border-left:4px solid transparent}.header{padding:5px;height:35px;width:100%}.header .hints{position:absolute;right:0}.header .type{margin-left:10px;width:70px;text-align:center;border-radius:10px;font-size:10px;margin-top:5px}.header .logo span,.header .tops{display:inline-block;margin-left:20px;vertical-align:middle}.header .logo-img{width:80px;height:30px;margin-top:-2px}.header .logo span button,.header .logo span button:active,.header .logo span button:active:hover{border:none}.header .logo span .dropdown-menu li a{padding:8px 30px}.header .logo span .dropdown-menu .divider{margin:0}.contents .attr{height:96vh;padding:0!important}.contents .right{word-wrap:break-word;font-weight:600!important}.left .content-nav .top-tog{font-weight:500;width:33%;text-align:center;margin-left:-8px;padding-top:5px;font-size:12px;padding-bottom:5px;text-transform:uppercase}.left .content-nav .top-tog:first-child{padding-left:10px;border-left:none}.left .content-nav .top-tog:last-child{margin-left:-8px;border-right:none}.left .content-body{height:95%}.left .content-body .loops{margin-top:3px}.left .content-body .loops .padder{padding:20px 50px}.left .content-body .loops .loop-tog{width:100%;display:none}.left .content-body .loops .holder i{float:right;margin-top:10px;font-size:10px;border-radius:10px;padding:1px 10px;font-style:normal}.left .content-body .loops .holder{padding:5px 10px}.left .content-body .loops div span{display:block}.left .content-body .loops div .name{font-weight:500;padding-left:10px}.left .content-body .loops div .path{font-size:11px}.left .content-body .loops .id{width:15px;font-size:10px;text-align:center;border:none;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;position:absolute;left:4px;padding-top:5px;padding-bottom:5px;display:inline-block;vertical-align:middle}.left .content-body .loops .active{display:block}.exception-type{padding:10px}.exception-type>.action,.exception-type>span{display:inline-block}.exception-type>.action{float:right}.exception-type>.action span{cursor:pointer;font-size:12px;padding:0 10px 2px;border-radius:10px}.exception-type>span{border-radius:10px;padding:3px 10px}.exception-msg{padding:10px;font-size:15px}.browser-view,.code-view{height:87.3vh}.code-view table{font-family:InconsolataRegular!important;font-size:12px;font-weight:500;width:100%;white-space:nowrap;table-layout:fixed;border-collapse:collapse}.code-view table .keyword, .code-view table .function{font-family:InconsolataBold !important;font-weight:500}.code-view table tr td:first-child{width:60px;text-align:center}.code-view table tr .line-content{padding-left:5px}.code-view table .highlighted td{padding:8px 0!important}.code-view table .highlighted td:first-child{border:none;font-weight:700}.contents .right .global:first-child{border-top:none}.contents .right .global .env-arr{padding-left:20px}.contents .right .global .env-arr .key{color:#3689b2}.contents .right .global .content{cursor:default}.contents .right .global .content .caret{cursor:pointer}.contents .right .global .content,.contents .right .global .labeled{display:block}.contents .right .global .labeled{font-weight:500;padding:10px}.contents .listed{padding:5px 10px}.contents .right .global .content{padding:5px 0}.contents .listed .index,.contents .listed .value{font-weight:500;font-size:10px}.mCustomScrollbar{-ms-touch-action:pinch-zoom;touch-action:pinch-zoom}.mCustomScrollbar.mCS_no_scrollbar,.mCustomScrollbar.mCS_touch_action{-ms-touch-action:auto;touch-action:auto}.mCustomScrollBox{position:relative;overflow:hidden;height:100%;max-width:100%;outline:0;direction:ltr}.mCSB_container{overflow:hidden;width:auto;height:auto}.mCSB_container.mCS_no_scrollbar_y.mCS_y_hidden{margin-right:0}.mCS-dir-rtl>.mCSB_inside>.mCSB_container{margin-right:0;margin-left:30px}.mCS-dir-rtl>.mCSB_inside>.mCSB_container.mCS_no_scrollbar_y.mCS_y_hidden{margin-left:0}.mCSB_scrollTools{position:absolute;width:16px;height:auto;left:auto;top:0;right:0;bottom:0;opacity:.75;filter:"alpha(opacity=75)";-ms-filter:"alpha(opacity=75)"}.mCSB_outside+.mCSB_scrollTools{right:-26px}.mCS-dir-rtl>.mCSB_inside>.mCSB_scrollTools,.mCS-dir-rtl>.mCSB_outside+.mCSB_scrollTools{right:auto;left:0}.mCS-dir-rtl>.mCSB_outside+.mCSB_scrollTools{left:-26px}.mCSB_scrollTools .mCSB_draggerContainer{position:absolute;top:0;left:0;bottom:0;right:0;height:auto}.mCSB_scrollTools a+.mCSB_draggerContainer{margin:20px 0}.mCSB_scrollTools .mCSB_draggerRail{width:2px;height:100%;margin:0 auto;-webkit-border-radius:16px;-moz-border-radius:16px;border-radius:16px}.mCSB_scrollTools .mCSB_dragger{cursor:pointer;width:100%;height:30px;z-index:1}.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar{position:relative;width:4px;height:100%;margin:0 auto;-webkit-border-radius:16px;-moz-border-radius:16px;border-radius:16px;text-align:center}.mCSB_scrollTools_vertical.mCSB_scrollTools_onDrag_expand .mCSB_dragger.mCSB_dragger_onDrag_expanded .mCSB_dragger_bar,.mCSB_scrollTools_vertical.mCSB_scrollTools_onDrag_expand .mCSB_draggerContainer:hover .mCSB_dragger .mCSB_dragger_bar{width:12px}.mCSB_scrollTools_vertical.mCSB_scrollTools_onDrag_expand .mCSB_dragger.mCSB_dragger_onDrag_expanded+.mCSB_draggerRail,.mCSB_scrollTools_vertical.mCSB_scrollTools_onDrag_expand .mCSB_draggerContainer:hover .mCSB_draggerRail{width:8px}.mCSB_scrollTools .mCSB_buttonDown,.mCSB_scrollTools .mCSB_buttonUp{display:block;position:absolute;height:20px;width:100%;overflow:hidden;margin:0 auto;cursor:pointer}.mCSB_scrollTools .mCSB_buttonDown{bottom:0}.mCSB_horizontal.mCSB_inside>.mCSB_container{margin-right:0;margin-bottom:30px}.mCSB_horizontal.mCSB_outside>.mCSB_container{min-height:100%}.mCSB_horizontal>.mCSB_container.mCS_no_scrollbar_x.mCS_x_hidden{margin-bottom:0}.mCSB_scrollTools.mCSB_scrollTools_horizontal{width:auto;height:16px;top:auto;right:0;bottom:0;left:0}.mCustomScrollBox+.mCSB_scrollTools+.mCSB_scrollTools.mCSB_scrollTools_horizontal,.mCustomScrollBox+.mCSB_scrollTools.mCSB_scrollTools_horizontal{bottom:-26px}.mCSB_scrollTools.mCSB_scrollTools_horizontal a+.mCSB_draggerContainer{margin:0 20px}.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_draggerRail{width:100%;height:2px;margin:7px 0}.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_dragger{width:30px;height:100%;left:0}.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_dragger .mCSB_dragger_bar{width:100%;height:4px;margin:6px auto}.mCSB_scrollTools_horizontal.mCSB_scrollTools_onDrag_expand .mCSB_dragger.mCSB_dragger_onDrag_expanded .mCSB_dragger_bar,.mCSB_scrollTools_horizontal.mCSB_scrollTools_onDrag_expand .mCSB_draggerContainer:hover .mCSB_dragger .mCSB_dragger_bar{height:12px;margin:2px auto}.mCSB_scrollTools_horizontal.mCSB_scrollTools_onDrag_expand .mCSB_dragger.mCSB_dragger_onDrag_expanded+.mCSB_draggerRail,.mCSB_scrollTools_horizontal.mCSB_scrollTools_onDrag_expand .mCSB_draggerContainer:hover .mCSB_draggerRail{height:8px;margin:4px 0}.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_buttonLeft,.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_buttonRight{display:block;position:absolute;width:20px;height:100%;overflow:hidden;margin:0 auto;cursor:pointer}.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_buttonLeft{left:0}.mCSB_scrollTools.mCSB_scrollTools_horizontal .mCSB_buttonRight{right:0}.mCSB_container_wrapper{position:absolute;height:auto;width:auto;overflow:hidden;top:0;left:0;right:0;bottom:0;margin-right:30px;margin-bottom:30px}.mCSB_container_wrapper>.mCSB_container{padding-right:30px;padding-bottom:30px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}.mCSB_vertical_horizontal>.mCSB_scrollTools.mCSB_scrollTools_vertical{bottom:20px}.mCSB_vertical_horizontal>.mCSB_scrollTools.mCSB_scrollTools_horizontal{right:20px}.mCSB_container_wrapper.mCS_no_scrollbar_x.mCS_x_hidden+.mCSB_scrollTools.mCSB_scrollTools_vertical{bottom:0}.mCS-dir-rtl>.mCustomScrollBox.mCSB_vertical_horizontal.mCSB_inside>.mCSB_scrollTools.mCSB_scrollTools_horizontal,.mCSB_container_wrapper.mCS_no_scrollbar_y.mCS_y_hidden+.mCSB_scrollTools~.mCSB_scrollTools.mCSB_scrollTools_horizontal{right:0}.mCS-dir-rtl>.mCustomScrollBox.mCSB_vertical_horizontal.mCSB_inside>.mCSB_scrollTools.mCSB_scrollTools_horizontal{left:20px}.mCS-dir-rtl>.mCustomScrollBox.mCSB_vertical_horizontal.mCSB_inside>.mCSB_container_wrapper.mCS_no_scrollbar_y.mCS_y_hidden+.mCSB_scrollTools~.mCSB_scrollTools.mCSB_scrollTools_horizontal{left:0}.mCS-dir-rtl>.mCSB_inside>.mCSB_container_wrapper{margin-right:0;margin-left:30px}.mCSB_container_wrapper.mCS_no_scrollbar_y.mCS_y_hidden>.mCSB_container{padding-right:0}.mCSB_container_wrapper.mCS_no_scrollbar_x.mCS_x_hidden>.mCSB_container{padding-bottom:0}.mCustomScrollBox.mCSB_vertical_horizontal.mCSB_inside>.mCSB_container_wrapper.mCS_no_scrollbar_y.mCS_y_hidden{margin-right:0;margin-left:0}.mCustomScrollBox.mCSB_vertical_horizontal.mCSB_inside>.mCSB_container_wrapper.mCS_no_scrollbar_x.mCS_x_hidden{margin-bottom:0}.mCS-autoHide>.mCustomScrollBox>.mCSB_scrollTools,.mCS-autoHide>.mCustomScrollBox~.mCSB_scrollTools{opacity:0;filter:"alpha(opacity=0)";-ms-filter:"alpha(opacity=0)"}.mCS-autoHide:hover>.mCustomScrollBox>.mCSB_scrollTools,.mCS-autoHide:hover>.mCustomScrollBox~.mCSB_scrollTools,.mCustomScrollBox:hover>.mCSB_scrollTools,.mCustomScrollBox:hover~.mCSB_scrollTools,.mCustomScrollbar>.mCustomScrollBox>.mCSB_scrollTools.mCSB_scrollTools_onDrag,.mCustomScrollbar>.mCustomScrollBox~.mCSB_scrollTools.mCSB_scrollTools_onDrag{opacity:1;filter:"alpha(opacity=100)";-ms-filter:"alpha(opacity=100)"}.mCSB_outside+.mCS-minimal-dark.mCSB_scrollTools_vertical,.mCSB_outside+.mCS-minimal.mCSB_scrollTools_vertical{right:0;margin:12px 0}.mCustomScrollBox.mCS-minimal+.mCSB_scrollTools+.mCSB_scrollTools.mCSB_scrollTools_horizontal,.mCustomScrollBox.mCS-minimal+.mCSB_scrollTools.mCSB_scrollTools_horizontal,.mCustomScrollBox.mCS-minimal-dark+.mCSB_scrollTools+.mCSB_scrollTools.mCSB_scrollTools_horizontal,.mCustomScrollBox.mCS-minimal-dark+.mCSB_scrollTools.mCSB_scrollTools_horizontal{bottom:0;margin:0 12px}.mCS-dir-rtl>.mCSB_outside+.mCS-minimal-dark.mCSB_scrollTools_vertical,.mCS-dir-rtl>.mCSB_outside+.mCS-minimal.mCSB_scrollTools_vertical{left:0;right:auto}.mCS-minimal-dark.mCSB_scrollTools .mCSB_draggerRail,.mCS-minimal.mCSB_scrollTools .mCSB_draggerRail{background-color:transparent}.mCS-minimal-dark.mCSB_scrollTools_vertical .mCSB_dragger,.mCS-minimal.mCSB_scrollTools_vertical .mCSB_dragger{height:50px}.mCS-minimal-dark.mCSB_scrollTools_horizontal .mCSB_dragger,.mCS-minimal.mCSB_scrollTools_horizontal .mCSB_dragger{width:50px}.mCS-minimal.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar{background-color:#fff;background-color:rgba(255,255,255,.2);filter:"alpha(opacity=20)";-ms-filter:"alpha(opacity=20)"}.mCS-minimal.mCSB_scrollTools .mCSB_dragger.mCSB_dragger_onDrag .mCSB_dragger_bar,.mCS-minimal.mCSB_scrollTools .mCSB_dragger:active .mCSB_dragger_bar{background-color:#fff;background-color:rgba(255,255,255,.5);filter:"alpha(opacity=50)";-ms-filter:"alpha(opacity=50)"}.mCS-minimal-dark.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar{background-color:#000;background-color:rgba(0,0,0,.2);filter:"alpha(opacity=20)";-ms-filter:"alpha(opacity=20)"}.mCS-minimal-dark.mCSB_scrollTools .mCSB_dragger.mCSB_dragger_onDrag .mCSB_dragger_bar,.mCS-minimal-dark.mCSB_scrollTools .mCSB_dragger:active .mCSB_dragger_bar{background-color:#000;background-color:rgba(0,0,0,.5);filter:"alpha(opacity=50)";-ms-filter:"alpha(opacity=50)"}   
            ' . $theme . '
            .header .logo .logo-img {background: url(data:image/png;base64,' . $image . ') no-repeat;background-size: contain;}
        </style>
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
    ' . $content . '
    <script>' . file_get_contents($theme_file . 'js.js') . '</script>
    </body>
</html>';
    }

}