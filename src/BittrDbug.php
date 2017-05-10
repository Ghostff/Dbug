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

    public function __construct($type = null, string $theme_or_log_path = null, int $line_range = 20)
    {
        ob_start();

        if ( $type == null || is_string($type))
        {
            $this->path = $theme_or_log_path;
            if ($type == 'prettify')
            {
                $this->theme = $theme_or_log_path;
                if (Highlight::theme($theme_or_log_path, 'yola') == 1)
                {
                    $this->theme = 'yola';
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

        $content = sprintf($this->template(),
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
        $file_name = basename($file);
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
                $file = $traces['file'];
                $line = $traces['line'];
            }
            else
            {
                $class = $traces['function'];
                $namespace = '_GLOBAL';
                $type = '()';
                $file = $traces['file'] ?? '';
                $line = $traces['line'] ?? '';
                $function = '';
            }

            $dsc = 'title="'. $namespace . '" data-file="' . $file . '"data-class="' . $class . '"
                    data-type="' . $type . '" data-function="' . $function . '" data-line="' . $line . '"';
            $traced .= '<div class="function loop-tog" data-id="proc-' . $i . '" ' . $dsc . '>
                            <div class="id loop-tog code">' . $i . '</div>
                            <div class="holder">
                                <span class="name">'. $class . '<b>' . $type . '</b>' . $function . '<i class="line">' . $line . '</i></span>
                                <span class="path">' . $file . '</span> 
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

            $_code = rtrim($this->chunk($file, $line));

            $this->contents[] = '<div class="code-view" id="proc-' . $i . '" style="display:none;">' . Highlight::render($_code) . '</div>';
        }


        return '<div class="content-nav">
                    <div class="top-tog active" id="location">Location</div> 
                    <div class="top-tog" id="function">Trace</div> 
                    <div class="top-tog" id="memory">Memory</div> 
                </div>
                <div class="content-body">
                    <div class="loops">
                        <div class="location loop-tog active" data-id="proc-main" data-line="' . $line . '" data-file="' . $file . '">
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

    private function middle(string $type, string $message, string $file, int $line): string
    {
        $code = rtrim($this->chunk($file, $line));
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

    private function template(): string
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

    private function html(string $content): string
    {
        $DIRS = DIRECTORY_SEPARATOR;
        $theme_file = __DIR__ . $DIRS . 'Styles' . $DIRS;
        $theme = file_get_contents($theme_file . $this->theme . '.css');
        $image = base64_encode(file_get_contents($theme_file . $this->theme . '.png'));
        $font_reg = base64_encode(file_get_contents($theme_file . 'fonts' . $DIRS . 'InconsolataRegular.ttf'));
        $font_bld = base64_encode(file_get_contents($theme_file . 'fonts' . $DIRS . 'InconsolataBold.ttf'));

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>BittrDbug Debug</title>
        <style>
            @font-face{font-family:InconsolataRegular;src:url(data:font/truetype;charset=utf-8;base64,' . $font_reg . ') format("truetype");}
            @font-face{font-family:InconsolataBold;src:url(data:font/truetype;charset=utf-8;base64,' . $font_bld . ') format("truetype");}
               
            ' . file_get_contents($theme_file . 'bts.css') . $theme . '
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