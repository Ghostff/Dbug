<?php

namespace Debug;

class BittrDbug
{
    private $contents = [];

    private $type = null;

    private $chunk = 0;

    private $time = 0;

    private $theme = null;

    private $path = null;

    const FILE_LOG = 'fileLog';

    const PRETTIFY = 'prettify';

    public function __construct($type = null, string $theme_or_log_path = null, int $line_range = 10)
    {
        ob_start();

        if ( $type == null || is_string($type))
        {
            $this->path = $theme_or_log_path;
            if ($type == 'prettify')
            {
                if ($theme_or_log_path == null)
                {
                    $this->theme = 'default';
                }
                else
                {
                    $this->theme = $theme_or_log_path;
                    if (Highlight::theme($theme_or_log_path, 'default') == 1)
                    {
                        $this->theme = 'default';
                    }
                }

            }
            $type = [$this, $type];
        }

        $this->chunk = $line_range;

        $this->time = microtime(true);
        set_exception_handler($type);
        set_error_handler([$this, 'handle']);

    }

    public function prettify(\Throwable $e)
    {
        $type = $this->type;
        if ($type !== null)
        {
            $this->type = null;
        }
        else
        {
            $type = get_class($e);
        }

        $content = sprintf('<div class="header">%s</div>
        <div class="container-fluid">
            <div class="row contents">
                <div class="col-md-3 attr left">%s</div>
                <div class="col-md-6 attr middle ">%s</div>
                <div class="col-md-3 attr right">%s</div>
            </div>
        </div>',
            $this->top(),
            $this->left($e->getFile(), $e->getLine(), $e->getCode(), $e->getTrace()),
            $this->middle($type, $e->getMessage(), $e->getFile(), $e->getLine()),
            $this->right()
        );

        echo $this->html($content); exit;
    }

    public function fileLog(\Throwable $e)
    {
        $type = $this->type;
        if ($type !== null)
        {
            $this->type = null;
        }
        else
        {
            $type = get_class($e);
        }

        $template = '[%s] [%s] --- %s --- %s:%d [%s]' . PHP_EOL;
        $new_trace = '';

        $trace = $e->getTrace();

        $_trace = count($trace) - 1;
        for ($i = $_trace; $i >= 0; $i--)
        {
            $t = $trace[$i];
            if ( ! isset($t['file']))
            {
                continue;
            }

            if (isset($t['type']))
            {
                $peaces = explode('\\', $t['class']);
                $class = end($peaces);
                $function = $class . $t['type'] . $t['function'];
            }
            else
            {
                $function = $t['function'];
            }

            $new_trace .= '    [' . $function. '] ' . $t['file'] . ':' . $t['line'] . PHP_EOL;
        }

        $new_trace = PHP_EOL . $new_trace;
        $file = sprintf($template, date("d-m-Y H:i:s"), $type, $e->getMessage(), $e->getFile(), $e->getLine(), $new_trace);
        file_put_contents($this->path, $file, FILE_APPEND);
        ob_end_clean();
    }

    public function Handle(int $severity, string $message, string $filename, int $lineno)
    {
        $l = error_reporting();
        if ( $l & $severity ) {
            switch ($severity) {
                case E_USER_ERROR:
                    $type = 'Fatal Error';
                    break;
                case E_USER_WARNING:
                case E_WARNING:
                    $type = 'Warning';
                    break;
                case E_USER_NOTICE:
                case E_NOTICE:
                case @E_STRICT:
                    $type = 'Notice';
                    break;
                case @E_RECOVERABLE_ERROR:
                    $type = 'Catchable';
                    break;
                default:
                    $type = 'Unknown Error';
                    break;
            }
        }
        $this->type = $type;
        throw new \ErrorException($message, 0, $severity, $filename, $lineno);
    }

    private function chunk(string $file, int $line): string
    {
        $chunk_count = $this->chunk;
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

    private function highlight(string $message): string
    {
        return preg_replace('/(\'(.*?)\'|"(.*?)")/s', '<span class="char-string">$1</span>', $message);
    }

    private function objects($objects): string
    {
        $obj = new \ReflectionObject($objects);
        $temp = '';
        $format = '';

        foreach ($obj->getProperties() as $size => $prop)
        {
            if ($prop->isPrivate())
            {
                $format .= '<span class="private">private&nbsp;&nbsp; </span> : ';
            }
            elseif ($prop->isProtected())
            {
                $format .= '<span class="protected">protected </span> : ';
            }
            elseif ($prop->isPublic())
            {
                $format .= '<span class="private">private&nbsp;&nbsp;&nbsp; </span> : ';
            }

            $prop->setAccessible(true);
            $format .= $this->get($prop->getValue($objects)) . '; <br />';
        }

        $temp .= '<span class="char-object">' . $obj->getName() . '</span> [  <span class="caret"></span>  <div class="env-arr">';

        $temp .= $format . '</div>]';
        return $temp;
    }

    private function get($arguments, bool $array_loop = false): string
    {
        $arguments = [$arguments];
        $format = '';
        foreach ($arguments as $arg)
        {
            $type = gettype($arg);
            if ($type == 'string')
            {
                $arg =  str_replace('<', '&lt;', $arg);
                $format = '<span class="char-string">' . $arg . '</span>';
            }
            elseif ($type == 'integer')
            {
                $format = '<span class="char-integer">' . $arg . '</span>';
            }
            elseif ($type == 'boolean')
            {
                $arg = ($arg) ? 'true' : 'false';
                $format = '<span class="char-bool">' . $arg . '</span>';
            }
            elseif ($type == 'double')
            {
                $format = '<span class="char-double">' . $arg . '</span>';
            }
            elseif ($type == 'NULL')
            {
                $format = '<span class="char-null">null</span>';
            }
            elseif ($type == 'float')
            {
                $format = '<span class="char-float">' . $arg . '</span>';
            }
            elseif ($type == 'array')
            {
                $format .= '[  <span class="caret"></span>  <div class="env-arr">';

                foreach ($arg as $key => $value)
                {
                    $key = str_replace('<', '&lt;', $key);
                    if ( is_array($value))
                    {
                        $format .= '<span class="key">' . $key . '</span> : ' . $this->get($value, true) . ',<br />';
                    }
                    else
                    {
                        $format .= '<span class="key">' . $key . '</span> : ' . $this->get($value, true) . ',<br/>';
                    }
                }

                $format .= '</div>]';
            }
            elseif ($type == 'object')
            {
                $format .= $this->objects($arg);
            }
        }

        return $format;
    }

    private function top(): string
    {
        $selected_theme = $this->theme;
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

    private function left(string $file, string $line, int $code, array $trace = [])
    {
        $__file = explode(DIRECTORY_SEPARATOR, $file);
        $file_name = end($__file);
        $file_path = rtrim($file, $file_name);

        $start = $this->time;
        $traced = '';
        $memory = '';
        $_trace = count($trace) - 1;

        for ($i = 0; $i <= $_trace; $i++)
        {
            $traces = $trace[$i];
            if ( ! isset($traces['line']))
            {
                continue;
            }

            if(isset($traces['class']))
            {
                $peaces = explode('\\', $traces['class']);
                $class = end($peaces);
                $namespace = str_replace('\\', '\\', rtrim($traces['class'], $class));
                $type = $traces['type'];
                $function = $traces['function'];
                $_file = $traces['file'];
                $_line = $traces['line'];
            }
            else
            {
                $class = $traces['function'];
                $namespace = '_GLOBAL';
                $type = '()';
                $_file = $traces['file'] ?? '';
                $_line = $traces['line'] ?? '';
                $function = '';
            }

            $dsc = 'title="'. $namespace . '" data-file="' . $_file . '" data-class="' . $class . '"
                    data-type="' . $type . '" data-function="' . $function . '" data-line="' . $_line . '"';
            $traced .= '<div class="loop-tog" data-id="proc-' . $i . '" ' . $dsc . '>
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">'. $class . '<b>' . $type . '</b>' . $function . '<i class="line">' . $_line . '</i></span>
                                <span class="path">' . $_file . '</span> 
                            </div>   
                        </div>';


            $micro_time = microtime(true) - $start;

            $memory .= '<div class="memory loop-tog" data-id="proc-' . $i . '" ' . $dsc . '>
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">' . memory_get_usage() . '</span>
                                <span class="path"> ' .$micro_time . '</span> 
                            </div>
                       </div>';

            $_code = rtrim($this->chunk($_file, $_line));

            $this->contents[] = '<div class="code-view" id="proc-' . $i . '" style="display:none;">' . Highlight::render($_code) . '</div>';
        }

        return '<div class="content-nav" id="cont-nav">
                    <div class="top-tog active" id="location">Location</div> 
                    <div class="top-tog" id="function">Trace</div> 
                    <div class="top-tog" id="memory">Memory</div> 
                </div>
                <div class="content-body">
                    <div class="location loops active">
                        <div class="loop-tog" data-id="proc-main" data-line="' . $line . '" data-file="' . $file . '">
                            <div class="id loop-tog code">' . $code . '</div>
                            <div class="holder">
                                <span class="name">' . $file_name . '<i class="line">' . $line . '</i> </span>
                                <span class="path">' . $file_path . '</span>             
                            </div>   
                        </div>
                        <div class="loop-tog" data-id="proc-buffer">
                            <div class="holder">
                                <span class="name" style="padding-left: 0px;">Output Buffer</span>
                                <span class="path">Toggle contents sent to output buffer</span>             
                            </div>   
                        </div>
                    </div>
                    <div class="function loops">' . $traced . '</div><div class="memory loops">' .  $memory . '</div>
                </div> ';
    }

    private function middle(string $type, string $message, string $file, int $line): string
    {
        $code = rtrim($this->chunk($file, $line));
        $output = ob_get_clean();
        if ($output == '')
        {
            $output = '<h3 style="text-align: center;">No output sent to buffer</h3>';
        }

        $message = (strlen($message) > 0) ? $message : 'No message passed in ' . $type . ' construct';

        $g = 'php ' . $type . ' ' . $message;
        $s = '[php] ' . $message;
        return '<div class="exception-type">
                    <span>' . $type . '</span>
                    <div class="action">
                        <span title="lookup error message in stackoveflow" onclick="window.open(\'http://stackoverflow.com/search?q=' . $s . '\', \'_blank\')"><span class="caret"></span> stackoverflow</span>
                        <span title="lookup error message in google" onclick="window.open(\'https://www.google.com/search?q=' . $g . '\', \'_blank\')"><span class="caret"></span> google</span>
                    </div>
                </div>
                <div class="exception-msg">' . $this->highlight($message) . '</div>
                <div class="code-view" id="proc-main">' . Highlight::render($code) . '</div>
                <div class="browser-view" id="proc-buffer" style="overflow:auto;display: none">' . $output . '</div>' . implode($this->contents) . '
                <div class="active-desc" id="repop">
                    <div class="keyword">Class: <span class="char-null">null</span></div>
                    <div class="namespace">Namespace: <span class="char-null">null</span></div>
                    <div class="file">File: ' . $file . ':<span class="char-integer">' . $line . '</span></div>
                    
                </div>';
    }

    private function right(): string
    {
        $globals = array(
            'Server' => $_SERVER ?? [],
            'Get' => $_GET ?? [],
            'Post' => $_POST ?? [],
            'Files' => $_FILES ?? [],
            'Request' => $_REQUEST ?? [],
            'Session' => $_SESSION ?? [],
            'Cookie' => $_COOKIE ?? [],
            'Env' => $_ENV ?? []
        );

        $side = '';
        $count = 1;
        foreach ($globals as $names => $attributes)
        {
            $side .= '<div class="global"><div class="labeled" id="tog-' . $count . '"><span class="caret"></span> &nbsp;&nbsp; ' . $names . '</div>
                    <div class="content" style="display:none;">' . PHP_EOL;
            foreach ($attributes as $key => $values)
            {
                $side .= '<div class="listed">
                            <span class="index">' . $key . '</span> :
                            <span class="value">' . $this->get($values) . '</span>
                        </div>';
            }
            $side .= '</div></div>';
            $count++;
        }
        return $side;
    }

    private function html(string $content): string
    {
        $DIRS = DIRECTORY_SEPARATOR;
        $theme_file = __DIR__ . $DIRS . 'Styles' . $DIRS;
        $theme = file_get_contents($theme_file . $this->theme . '.css');
        $image = base64_encode(file_get_contents($theme_file . $this->theme . '.png'));
        $font_bld = base64_encode(file_get_contents($theme_file . 'fonts' . $DIRS . 'Inconsolata.woff2'));

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>BittrDbug Debug</title>
        <style>
            @font-face{font-family:Inconsolata;src:url(data:font/truetype;charset=utf-8;base64,' . $font_bld . ') format("woff2");}
            .contents .middle,.ss-wrapper,body{overflow:hidden}.contents .right .global,.left .content-body .loops .holder:hover,.left .content-nav .top-tog{cursor:pointer}.contents .listed:last-child,.contents .right .global:last-child{border-bottom:none}html{font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-size:10px;-webkit-tap-highlight-color:transparent}body{margin:0;font-family:Inconsolata;font-weight:700;font-size:14px;line-height:1.42857143;color:#333;background-color:#fff}.container,.container-fluid{margin-right:auto;margin-left:auto;padding-right:15px;padding-left:15px}article,aside,details,figcaption,figure,footer,header,hgroup,main,menu,nav,section,summary{display:block}.caret,.header .hints .type,.left .content-nav .top-tog{display:inline-block}*,:after,:before{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}button,input,select,textarea{font-family:inherit;font-size:inherit;line-height:inherit}a{color:#337ab7;text-decoration:none}a:focus,a:hover{color:#23527c;text-decoration:underline}a:focus{outline:-webkit-focus-ring-color auto 5px;outline-offset:-2px}@media (min-width:768px){.container{width:750px}}@media (min-width:992px){.container{width:970px}}@media (min-width:1200px){.container{width:1170px}}.row{margin-right:-15px;margin-left:-15px}.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9,.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9,.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9,.col-xs-1,.col-xs-10,.col-xs-11,.col-xs-12,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-5,.col-xs-6,.col-xs-7,.col-xs-8,.col-xs-9{position:relative;min-height:1px;padding-right:15px;padding-left:15px}.col-xs-1,.col-xs-10,.col-xs-11,.col-xs-12,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-5,.col-xs-6,.col-xs-7,.col-xs-8,.col-xs-9{float:left}.col-xs-12{width:100%}.col-xs-11{width:91.66666667%}.col-xs-10{width:83.33333333%}.col-xs-9{width:75%}.col-xs-8{width:66.66666667%}.col-xs-7{width:58.33333333%}.col-xs-6{width:50%}.col-xs-5{width:41.66666667%}.col-xs-4{width:33.33333333%}.col-xs-3{width:25%}.col-xs-2{width:16.66666667%}.col-xs-1{width:8.33333333%}.col-xs-pull-12{right:100%}.col-xs-pull-11{right:91.66666667%}.col-xs-pull-10{right:83.33333333%}.col-xs-pull-9{right:75%}.col-xs-pull-8{right:66.66666667%}.col-xs-pull-7{right:58.33333333%}.col-xs-pull-6{right:50%}.col-xs-pull-5{right:41.66666667%}.col-xs-pull-4{right:33.33333333%}.col-xs-pull-3{right:25%}.col-xs-pull-2{right:16.66666667%}.col-xs-pull-1{right:8.33333333%}.col-xs-pull-0{right:auto}.col-xs-push-12{left:100%}.col-xs-push-11{left:91.66666667%}.col-xs-push-10{left:83.33333333%}.col-xs-push-9{left:75%}.col-xs-push-8{left:66.66666667%}.col-xs-push-7{left:58.33333333%}.col-xs-push-6{left:50%}.col-xs-push-5{left:41.66666667%}.col-xs-push-4{left:33.33333333%}.col-xs-push-3{left:25%}.col-xs-push-2{left:16.66666667%}.col-xs-push-1{left:8.33333333%}.col-xs-push-0{left:auto}.col-xs-offset-12{margin-left:100%}.col-xs-offset-11{margin-left:91.66666667%}.col-xs-offset-10{margin-left:83.33333333%}.col-xs-offset-9{margin-left:75%}.col-xs-offset-8{margin-left:66.66666667%}.col-xs-offset-7{margin-left:58.33333333%}.col-xs-offset-6{margin-left:50%}.col-xs-offset-5{margin-left:41.66666667%}.col-xs-offset-4{margin-left:33.33333333%}.col-xs-offset-3{margin-left:25%}.col-xs-offset-2{margin-left:16.66666667%}.col-xs-offset-1{margin-left:8.33333333%}.col-xs-offset-0{margin-left:0}@media (min-width:768px){.col-sm-1,.col-sm-10,.col-sm-11,.col-sm-12,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-5,.col-sm-6,.col-sm-7,.col-sm-8,.col-sm-9{float:left}.col-sm-12{width:100%}.col-sm-11{width:91.66666667%}.col-sm-10{width:83.33333333%}.col-sm-9{width:75%}.col-sm-8{width:66.66666667%}.col-sm-7{width:58.33333333%}.col-sm-6{width:50%}.col-sm-5{width:41.66666667%}.col-sm-4{width:33.33333333%}.col-sm-3{width:25%}.col-sm-2{width:16.66666667%}.col-sm-1{width:8.33333333%}.col-sm-pull-12{right:100%}.col-sm-pull-11{right:91.66666667%}.col-sm-pull-10{right:83.33333333%}.col-sm-pull-9{right:75%}.col-sm-pull-8{right:66.66666667%}.col-sm-pull-7{right:58.33333333%}.col-sm-pull-6{right:50%}.col-sm-pull-5{right:41.66666667%}.col-sm-pull-4{right:33.33333333%}.col-sm-pull-3{right:25%}.col-sm-pull-2{right:16.66666667%}.col-sm-pull-1{right:8.33333333%}.col-sm-pull-0{right:auto}.col-sm-push-12{left:100%}.col-sm-push-11{left:91.66666667%}.col-sm-push-10{left:83.33333333%}.col-sm-push-9{left:75%}.col-sm-push-8{left:66.66666667%}.col-sm-push-7{left:58.33333333%}.col-sm-push-6{left:50%}.col-sm-push-5{left:41.66666667%}.col-sm-push-4{left:33.33333333%}.col-sm-push-3{left:25%}.col-sm-push-2{left:16.66666667%}.col-sm-push-1{left:8.33333333%}.col-sm-push-0{left:auto}.col-sm-offset-12{margin-left:100%}.col-sm-offset-11{margin-left:91.66666667%}.col-sm-offset-10{margin-left:83.33333333%}.col-sm-offset-9{margin-left:75%}.col-sm-offset-8{margin-left:66.66666667%}.col-sm-offset-7{margin-left:58.33333333%}.col-sm-offset-6{margin-left:50%}.col-sm-offset-5{margin-left:41.66666667%}.col-sm-offset-4{margin-left:33.33333333%}.col-sm-offset-3{margin-left:25%}.col-sm-offset-2{margin-left:16.66666667%}.col-sm-offset-1{margin-left:8.33333333%}.col-sm-offset-0{margin-left:0}}@media (min-width:992px){.col-md-1,.col-md-10,.col-md-11,.col-md-12,.col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7,.col-md-8,.col-md-9{float:left}.col-md-12{width:100%}.col-md-11{width:91.66666667%}.col-md-10{width:83.33333333%}.col-md-9{width:75%}.col-md-8{width:66.66666667%}.col-md-7{width:58.33333333%}.col-md-6{width:50%}.col-md-5{width:41.66666667%}.col-md-4{width:33.33333333%}.col-md-3{width:25%}.col-md-2{width:16.66666667%}.col-md-1{width:8.33333333%}.col-md-pull-12{right:100%}.col-md-pull-11{right:91.66666667%}.col-md-pull-10{right:83.33333333%}.col-md-pull-9{right:75%}.col-md-pull-8{right:66.66666667%}.col-md-pull-7{right:58.33333333%}.col-md-pull-6{right:50%}.col-md-pull-5{right:41.66666667%}.col-md-pull-4{right:33.33333333%}.col-md-pull-3{right:25%}.col-md-pull-2{right:16.66666667%}.col-md-pull-1{right:8.33333333%}.col-md-pull-0{right:auto}.col-md-push-12{left:100%}.col-md-push-11{left:91.66666667%}.col-md-push-10{left:83.33333333%}.col-md-push-9{left:75%}.col-md-push-8{left:66.66666667%}.col-md-push-7{left:58.33333333%}.col-md-push-6{left:50%}.col-md-push-5{left:41.66666667%}.col-md-push-4{left:33.33333333%}.col-md-push-3{left:25%}.col-md-push-2{left:16.66666667%}.col-md-push-1{left:8.33333333%}.col-md-push-0{left:auto}.col-md-offset-12{margin-left:100%}.col-md-offset-11{margin-left:91.66666667%}.col-md-offset-10{margin-left:83.33333333%}.col-md-offset-9{margin-left:75%}.col-md-offset-8{margin-left:66.66666667%}.col-md-offset-7{margin-left:58.33333333%}.col-md-offset-6{margin-left:50%}.col-md-offset-5{margin-left:41.66666667%}.col-md-offset-4{margin-left:33.33333333%}.col-md-offset-3{margin-left:25%}.col-md-offset-2{margin-left:16.66666667%}.col-md-offset-1{margin-left:8.33333333%}.col-md-offset-0{margin-left:0}}@media (min-width:1200px){.col-lg-1,.col-lg-10,.col-lg-11,.col-lg-12,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-5,.col-lg-6,.col-lg-7,.col-lg-8,.col-lg-9{float:left}.col-lg-12{width:100%}.col-lg-11{width:91.66666667%}.col-lg-10{width:83.33333333%}.col-lg-9{width:75%}.col-lg-8{width:66.66666667%}.col-lg-7{width:58.33333333%}.col-lg-6{width:50%}.col-lg-5{width:41.66666667%}.col-lg-4{width:33.33333333%}.col-lg-3{width:25%}.col-lg-2{width:16.66666667%}.col-lg-1{width:8.33333333%}.col-lg-pull-12{right:100%}.col-lg-pull-11{right:91.66666667%}.col-lg-pull-10{right:83.33333333%}.col-lg-pull-9{right:75%}.col-lg-pull-8{right:66.66666667%}.col-lg-pull-7{right:58.33333333%}.col-lg-pull-6{right:50%}.col-lg-pull-5{right:41.66666667%}.col-lg-pull-4{right:33.33333333%}.col-lg-pull-3{right:25%}.col-lg-pull-2{right:16.66666667%}.col-lg-pull-1{right:8.33333333%}.col-lg-pull-0{right:auto}.col-lg-push-12{left:100%}.col-lg-push-11{left:91.66666667%}.col-lg-push-10{left:83.33333333%}.col-lg-push-9{left:75%}.col-lg-push-8{left:66.66666667%}.col-lg-push-7{left:58.33333333%}.col-lg-push-6{left:50%}.col-lg-push-5{left:41.66666667%}.col-lg-push-4{left:33.33333333%}.col-lg-push-3{left:25%}.col-lg-push-2{left:16.66666667%}.col-lg-push-1{left:8.33333333%}.col-lg-push-0{left:auto}.col-lg-offset-12{margin-left:100%}.col-lg-offset-11{margin-left:91.66666667%}.col-lg-offset-10{margin-left:83.33333333%}.col-lg-offset-9{margin-left:75%}.col-lg-offset-8{margin-left:66.66666667%}.col-lg-offset-7{margin-left:58.33333333%}.col-lg-offset-6{margin-left:50%}.col-lg-offset-5{margin-left:41.66666667%}.col-lg-offset-4{margin-left:33.33333333%}.col-lg-offset-3{margin-left:25%}.col-lg-offset-2{margin-left:16.66666667%}.col-lg-offset-1{margin-left:8.33333333%}.col-lg-offset-0{margin-left:0}}.caret{width:0;height:0;margin-left:2px;vertical-align:middle;border-top:4px dashed;border-top:4px solid\9;border-right:4px solid transparent;border-left:4px solid transparent}.header{padding:5px;height:35px;width:100%}.header .hints{position:absolute;right:0}.header .type{margin-left:10px;width:70px;text-align:center;border-radius:10px;font-size:10px;margin-top:5px}.header .logo span,.header .tops{display:inline-block;margin-left:20px;vertical-align:middle}.header .logo-img{width:80px;height:30px;margin-top:-2px}.header .logo span button,.header .logo span button:active,.header .logo span button:active:hover{border:none}.header .logo span .dropdown-menu li a{padding:8px 30px}.header .logo span .dropdown-menu .divider{margin:0}.contents .attr{height:96vh;padding:0!important}.contents .right{word-wrap:break-word;font-weight:600!important}.left .content-nav .top-tog{font-weight:600;width:34%;text-align:center;margin-left:-8px;padding-top:5px;font-size:12px;padding-bottom:5px;text-transform:uppercase}.left .content-nav .top-tog:first-child{padding-left:10px;border-left:none;margin-right:-1px}.left .content-nav .top-tog:last-child{margin-left:-7px;border-right:none}.left .content-body{height:95%}.loop-tog{width:98%}.location .loop-tog{width:94%}.left .content-body .loops{margin-top:3px;width:100%;display:none}.left .content-body .loops .holder i{float:right;margin-top:10px;font-size:10px;border-radius:10px;padding:1px 10px;font-style:normal}.left .content-body .loops .holder{padding:5px 10px}.left .content-body .loops div span{display:block}.left .content-body .loops div .name{font-weight:600;padding-left:10px}.left .content-body .loops div .path{font-size:11px}.left .content-body .loops .id{width:15px;font-size:10px;text-align:center;border:none;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;position:absolute;left:4px;padding-top:5px;padding-bottom:5px;display:inline-block;vertical-align:middle}.left .content-body .loops.active{display:block}.exception-type{padding:10px}.exception-type>.action,.exception-type>span{display:inline-block}.exception-type>.action{float:right}.exception-type>.action span{cursor:pointer;font-size:12px;padding:0 10px 2px;border-radius:10px}.exception-type>span{border-radius:10px;padding:3px 10px}.exception-msg{padding:10px;font-size:13px}.active-desc{position:absolute;width:100%;bottom:0;padding:10px;font-size:12px;z-index:4!important}.active-desc .file b{font-size:10px;margin-top:-5px}.browser-view{height:90.3vh}.ss-content,.ss-wrapper{height:100%;position:relative}.code-view table{font-size:12px;font-weight:400;width:100%;white-space:nowrap;table-layout:fixed;border-collapse:collapse}.code-view table .function,.code-view table .keyword{font-weight:600}.code-view table tr td:first-child{width:60px;text-align:center}.code-view table tr .line-content{padding-left:5px}.code-view table .highlighted td{padding:8px 0!important}.code-view table .highlighted td:first-child{border:none;font-weight:700}.contents .right .global:first-child{border-top:none}.contents .right .global .env-arr{padding-left:20px}.contents .right .global .content{cursor:default}.contents .right .global .content .caret,.ss-scroll{cursor:pointer}.contents .right .global .content,.contents .right .global .labeled{display:block}.contents .right .global .labeled{font-weight:600;padding:10px}.contents .listed{padding:5px 14px 5px 10px}.contents .right .global .content{padding:5px 0;width:104%}.contents .listed .index,.contents .listed .value{font-weight:500;font-size:10px}.ss-wrapper{width:100%;float:left}.ss-content{width:104%;overflow:auto;box-sizing:border-box}.ss-scroll{position:relative;width:6px;border-radius:4px;top:0;z-index:2;opacity:0;transition:opacity .25s linear}.ss-hidden{display:none}.ss-container:hover .ss-scroll{opacity:1}.ss-grabbed{-o-user-select:none;-ms-user-select:none;-moz-user-select:none;-webkit-user-select:none;user-select:none}
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
    <script>' . file_get_contents($theme_file . 'min.js') . '</script>
    </body>
</html>';
    }

}