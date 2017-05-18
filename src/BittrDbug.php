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

        $content = sprintf('<div style="font-family:Inconsolata !important;font-weight:bold !important;line-height:1.3 !important;font-size:14px !important;">
        <div class="__BittrDebuger__header">%s</div>
            <div class="__BittrDebuger__container-fluid">
                <div class="__BittrDebuger__row __BittrDebuger__contents">
                    <div class="__BittrDebuger__col-md-3 __BittrDebuger__attr __BittrDebuger__left">%s</div>
                    <div class="__BittrDebuger__col-md-6 __BittrDebuger__attr __BittrDebuger__middle ">%s</div>
                    <div class="__BittrDebuger__col-md-3 __BittrDebuger__attr __BittrDebuger__right">%s</div>
                </div>
        </div></div>',
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
        Highlight::setHighlight($line, ['class' => '__BittrDebuger__highlighted'], true);
        $_code[$line-1] = $codes[$line-1];
        ksort($_code);

        return implode($_code);
    }

    private function highlight(string $message): string
    {
        return preg_replace('/(\'(.*?)\'|"(.*?)")/s', '<span class="__BittrDebuger__char-string">$1</span>', $message);
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

        $temp .= '<span class="char-object">' . $obj->getName() . '</span> 
        [  <span class="__BittrDebuger__caret"></span>  <div class="__BittrDebuger__env-arr">';

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
                $format = '<span class="__BittrDebuger__char-string">' . $arg . '</span>';
            }
            elseif ($type == 'integer')
            {
                $format = '<span class="__BittrDebuger__char-integer">' . $arg . '</span>';
            }
            elseif ($type == 'boolean')
            {
                $arg = ($arg) ? 'true' : 'false';
                $format = '<span class="__BittrDebuger__char-bool">' . $arg . '</span>';
            }
            elseif ($type == 'double')
            {
                $format = '<span class="__BittrDebuger__char-double">' . $arg . '</span>';
            }
            elseif ($type == 'NULL')
            {
                $format = '<span class="__BittrDebuger__char-null">null</span>';
            }
            elseif ($type == 'float')
            {
                $format = '<span class="__BittrDebuger__char-float">' . $arg . '</span>';
            }
            elseif ($type == 'array')
            {
                $format .= '[  <span class="__BittrDebuger__caret"></span>  <div class="__BittrDebuger__env-arr">';

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
            $select .= '<li><a href="?theme=' . $names . '">' . $names . '</a></li> <li role="separator" class="__BittrDebuger__divider"></li>';
        }

        return '<div class="__BittrDebuger__logo __BittrDebuger__tops">
            <span class="__BittrDebuger__ogo-img"></span>
            <span class="__BittrDebuger__theme">Theme: ' . $selected_theme . '</span>
        </div>
        <div class="__BittrDebuger__hints __BittrDebuger__tops">
            <div class="__BittrDebuger__type __BittrDebuger__type-object">OBJECT</div>
            <div class="__BittrDebuger__type __BittrDebuger__type-null">NULL</div>
            <div class="__BittrDebuger__type __BittrDebuger__type-bool">BOOL</div>
            <div class="__BittrDebuger__type __BittrDebuger__type-array">ARRAY</div>
            <div class="__BittrDebuger__type __BittrDebuger__type-float">FLOAT</div>
            <div class="__BittrDebuger__type __BittrDebuger__type-double">DOUBLE</div>
            <div class="__BittrDebuger__type __BittrDebuger__type-string">STRING</div>
            <div class="__BittrDebuger__type __BittrDebuger__type-integer">INTEGER</div>
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

            $dsc = 'title="'. $namespace . '" data-file="' . $_file . '"data-class="' . $class . '"
                    data-type="' . $type . '" data-function="' . $function . '" data-line="' . $_line . '"';
            $traced .= '<div class="__BittrDebuger__loop-tog __BittrDebuger__l-parent" data-id="proc-' . $i . '" ' . $dsc . '>
                            <div class="__BittrDebuger__id __BittrDebuger__loop-tog __BittrDebuger__code">' . $i . '</div>
                            <div class="__BittrDebuger__holder">
                                <span class="__BittrDebuger__name">'. $class . '<b>' . $type .
                                '</b>' . $function . '<i class="__BittrDebuger__line">' . $_line . '</i></span>
                                <span class="__BittrDebuger__path">' . $_file . '</span> 
                            </div>   
                        </div>';


            $micro_time = microtime(true) - $start;

            $memory .= '<div class="__BittrDebuger__memory __BittrDebuger__loop-tog __BittrDebuger__l-parent" data-id="proc-' . $i . '" ' . $dsc . '>
                            <div class="__BittrDebuger__id __BittrDebuger__loop-tog __BittrDebuger__code">' . $i . '</div>
                            <div class="__BittrDebuger__holder">
                                <span class="__BittrDebuger__name">' . memory_get_usage() . '</span>
                                <span class="__BittrDebuger__path"> ' .$micro_time . '</span> 
                            </div>
                       </div>';

            $_code = rtrim($this->chunk($_file, $_line));

            $this->contents[] = '<div class="__BittrDebuger__code-view" id="proc-' . $i . '" style="display:none;">' . Highlight::render($_code) . '</div>';
        }

        return '<div class="__BittrDebuger__content-nav" id="cont-nav">
                    <div class="__BittrDebuger__top-tog __BittrDebuger__active" id="__BittrDebuger__location">Location</div> 
                    <div class="__BittrDebuger__top-tog" id="__BittrDebuger__function">Trace</div> 
                    <div class="__BittrDebuger__top-tog" id="__BittrDebuger__memory">Memory</div> 
                </div>
                <div class="__BittrDebuger__content-body">
                    <div class="__BittrDebuger__location __BittrDebuger__loops __BittrDebuger__active">
                        <div class="__BittrDebuger__loop-tog __BittrDebuger__l-parent" data-id="proc-main" data-line="' . $line . '" data-file="' . $file . '">
                            <div class="__BittrDebuger__id __BittrDebuger__loop-tog __BittrDebuger__code">' . $code . '</div>
                            <div class="__BittrDebuger__holder">
                                <span class="__BittrDebuger__name">' . $file_name . '<i class="line">' . $line . '</i> </span>
                                <span class="__BittrDebuger__path">' . $file_path . '</span>             
                            </div>   
                        </div>
                        <div class="__BittrDebuger__loop-tog __BittrDebuger__l-parent" data-id="proc-buffer">
                            <div class="__BittrDebuger__holder">
                                <span class="__BittrDebuger__name" style="padding-left: 0px;">Output Buffer</span>
                                <span class="__BittrDebuger__path">Toggle contents sent to output buffer</span>             
                            </div>   
                        </div>
                    </div>
                    <div class="__BittrDebuger__function __BittrDebuger__loops">' . $traced . '</div><div class="__BittrDebuger__memory __BittrDebuger__loops">' .  $memory . '</div>
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
        return '<div class="__BittrDebuger__exception-type">
                    <span>' . $type . '</span>
                    <div class="__BittrDebuger__action">
                        <span title="lookup error message in stackoveflow" onclick="window.open(\'http://stackoverflow.com/search?q=' . $s . '\', \'_blank\')"><span class="__BittrDebuger__caret"></span> stackoverflow</span>
                        <span title="lookup error message in google" onclick="window.open(\'https://www.google.com/search?q=' . $g . '\', \'_blank\')"><span class="__BittrDebuger__caret"></span> google</span>
                    </div>
                </div>
                <div class="__BittrDebuger__exception-msg">' . $this->highlight($message) . '</div>
                <div class="__BittrDebuger__code-view" id="proc-main">' . Highlight::render($code) . '</div>
                <div class="browser-view" id="proc-buffer" style="overflow:auto;display: none">' . $output . '</div>' . implode($this->contents) . '
                <div class="__BittrDebuger__active-desc" id="repop">
                    <div class="__BittrDebuger__keyword">Class: <span class="__BittrDebuger__char-null">null</span></div>
                    <div class="__BittrDebuger__namespace">Namespace: <span class="__BittrDebuger__char-null">null</span></div>
                    <div class="__BittrDebuger__file">File: ' . $file . ':<span class="__BittrDebuger__char-integer">' . $line . '</span></div>
                    
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
            $side .= '<div class="__BittrDebuger__global">
                    <div class="__BittrDebuger__labeled" id="tog-' . $count . '"><span class="__BittrDebuger__caret"></span> &nbsp;&nbsp; ' . $names . '</div>
                    <div class="__BittrDebuger__content" style="display:none;">' . PHP_EOL;
            foreach ($attributes as $key => $values)
            {
                $side .= '<div class="__BittrDebuger__listed">
                            <span class="__BittrDebuger__index">' . $key . '</span> :
                            <span class="__BittrDebuger__value">' . $this->get($values) . '</span>
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
            ' . file_get_contents($theme_file . 'min.css') . $theme . '
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