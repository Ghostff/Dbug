<?php

/**
 * Bittr
 *
 * @license
 *
 * New BSD License
 *
 * Copyright (c) 2017, ghostff community
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *      1. Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *      2. Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *      3. All advertising materials mentioning features or use of this software
 *      must display the following acknowledgement:
 *      This product includes software developed by the ghostff.
 *      4. Neither the name of the ghostff nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY ghostff ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL GHOSTFF COMMUNITY BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */
 
declare(strict_types=1);

namespace Dbug;

use Throwable;

class BittrDbug
{
    private $time = 0;
    private $code = null;
    private $path = null;
    private $type = null;
    private $file = null;
    private $line = null;
    private $chunk = 0;
    private $trace = [];
    private $theme = null;
    private $method = null;
    private $message = null;
    private $contents = [];
    private $trace_string = null;

    const FILE_LOG = 'fileLog';
    const PRETTIFY = 'prettify';

    /**
     * BittrDbug constructor.
     * @param mixed $type
     * @param string|null $theme_or_log_path
     * @param int $line_range
     */
    public function __construct($type, string $theme_or_log_path = null, int $line_range = 10)
    {
        ob_start();
        $this->method = $type;
        if (is_string($type))
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
        register_shutdown_function(function()
        {
            if (! is_null($error = error_get_last()))
            {
                ob_end_clean();
                ob_start();
                $this->handle($error['type'], $error['message'], $error['file'], $error['line']);
            }
        });

    }

    /**
     * @param Throwable $exception
     * @internal param Throwable $e
     */
    private function prettify(Throwable $exception)
    {
        $this->render($exception, [], get_class($exception));
    }

    /**
     * @param Throwable|null $exception
     * @param array $error
     * @param string|null $type
     */
    private function render(Throwable $exception = null, array $error = [], string $type = null)
    {
        if ($exception)
        {
            $file = $exception->getFile();
            $line = $exception->getLine();
            $code = $exception->getCode();
            $trace = $exception->getTrace();
            $message = $exception->getMessage();
        }
        else
        {
            $file = $error['file'];
            $line = $error['line'];
            $code = $error['code'];
            $trace = $error['trace'];
            $message = $error['message'];
        }

        if ($this->method == self::FILE_LOG)
        {
            $this->fileLog($message, $file, $line, $type, $trace);
            return;
        }
        elseif (is_callable($this->method))
        {
            $class = $this->method;
            $clone = clone $this;
            $clone->message = $message;
            $clone->file = $file;
            $clone->line = $line;
            $clone->trace = $trace;
            $clone->code = $code;
            $clone->trace_string = $this->traceAsString($trace);
            return $class($clone);
        }
        else
        {
            $content = sprintf('<div style="font-family:Inconsolata !important;font-weight:bold !important;line-height:1.3 !important;font-size:14px !important;">
            <div class="__BtrD__header">%s</div>
                <div class="__BtrD__container-fluid">
                    <div class="__BtrD__row __BtrD__contents">
                        <div class="__BtrD__col-md-3 __BtrD__attr __BtrD__left">%s</div>
                        <div class="__BtrD__col-md-6 __BtrD__attr __BtrD__middle ">%s</div>
                        <div class="__BtrD__col-md-3 __BtrD__attr __BtrD__right">%s</div>
                    </div>
            </div></div>',
                $this->top(),
                $this->left($file, (string) $line, (string) $code, $trace),
                $this->middle($type, $message, $file, $line),
                $this->right()
            );

            exit($this->html($content));
        }
    }

    /**
     * @param array $trace_array
     * @return string
     */
    private function traceAsString(array $trace_array): string
    {
        $trace = '';
        foreach ($trace_array as $count => $value)
        {
            if (! isset($value['file']))
            {
                $trace .= '    {main}';
                continue;
            }

            if (isset($value['class']))
            {
                $trace .= "    #{$count} {$value['class']}{$value['type']}{$value['function']}() called at {$value['file']}:{$value['line']}\n";
            }
            else
            {
                $trace .= "    #{$count} {$value['function']}() called at {$value['file']}:{$value['line']}\n";
            }
        }
        return $trace;
    }

    /**
     * @param string $message
     * @param string $file
     * @param int $line
     * @param string $type
     * @param array $trace
     * @internal param Throwable $e
     */
    private function fileLog(string $message, string $file, int $line, string $type, array $trace)
    {
        $log = $this->traceAsString($trace);
        $date = date('d-m-Y H:i:s');
        $template = "({$type}) {$message}\nFile : {$file}:{$line}\nDate : {$date}\nTrace: [\n{$log}]\n\n";

        $path = $this->path;
        $DS = DIRECTORY_SEPARATOR;

        $split = explode('-', $date);
        $day = $split[0];
        $month = $split[1];
        $year = strstr($split[2], ' ', true);

        $path = "{$path}{$DS}{$year}{$DS}{$month}{$DS}";
        if (! is_dir($path))
        {
            mkdir($path, 0777, true);
        }
        file_put_contents("{$path}{$day}.log", $template, FILE_APPEND);
    }

    /**
     * @param int $severity
     * @param string $message
     * @param string $file_name
     * @param int $line_no
     * @internal param string $filename
     * @internal param int $lineno
     */
    public function handle(int $severity, string $message, string $file_name, int $line_no)
    {
        if (error_reporting() & $severity )
        {
            switch ($severity)
            {
                case E_ERROR:
                    $type = 'Fatal Error';
                    break;
                case E_WARNING:
                    $type = 'Warning';
                    break;
                case E_PARSE:
                    $type = 'Parse Error';
                    break;
                case E_NOTICE:
                    $type = 'Notice Error';
                    break;
                case E_CORE_ERROR:
                    $type = 'Core Error';
                    break;
                case E_CORE_WARNING:
                    $type = 'Core Warning';
                    break;
                case E_COMPILE_ERROR:
                    $type = 'Compile Error';
                    break;
                case E_COMPILE_WARNING:
                    $type = 'Compile Warning';
                    break;
                case E_USER_ERROR:
                    $type = 'User Fatal Error';
                    break;
                case E_USER_WARNING:
                    $type = 'User Warning';
                    break;
                case E_USER_NOTICE:
                    $type = 'User Notice';
                    break;
                case E_STRICT:
                    $type = 'Strict';
                    break;
                case E_RECOVERABLE_ERROR:
                    $type = 'Recoverable Error';
                    break;
                case E_DEPRECATED:
                    $type = 'Deprecated Error';
                    break;
                case E_USER_DEPRECATED:
                    $type = 'User Deprecated Error';
                    break;
                default:
                    $type = 'Unknown Error';
                    break;
            }
        }

        if (! isset($type))
        {
            return;
        }
        $this->type = $type;

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($trace);
        $this->render(null, [
            'file' => $file_name,
            'line' => $line_no,
            'code' => 0,
            'trace' => $trace,
            'message' => $message
        ], $type);
    }

    /**
     * @param string $file
     * @param int $line
     * @return string
     */
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
        Highlight::setHighlight($line, ['class' => '__BtrD__highlighted'], true);
        $_code[$line-1] = $codes[$line-1];
        ksort($_code);

        return implode($_code);
    }

    /**
     * @param string $message
     * @return string
     */
    private function highlight(string $message): string
    {
        return preg_replace('/(\'(.*?)(?<!\\\)\'|"(.*?)(?<!\\\)")/s', '<span class="__BtrD__char-string">$1</span>', $message);
    }

    /**
     * @param $objects
     * @return string
     */
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
        [  <span class="__BtrD__caret"></span>  <div class="__BtrD__env-arr">';

        $temp .= $format . '</div>]';
        return $temp;
    }

    /**
     * @param $arguments
     * @return string
     */
    private function get($arguments): string
    {
        $arguments = [$arguments];
        $format = '';
        foreach ($arguments as $arg)
        {
            $type = gettype($arg);
            if ($type == 'string')
            {
                $arg =  str_replace('<', '&lt;', $arg);
                $format = '<span class="__BtrD__char-string">' . $arg . '</span>';
            }
            elseif ($type == 'integer')
            {
                $format = '<span class="__BtrD__char-integer">' . $arg . '</span>';
            }
            elseif ($type == 'boolean')
            {
                $arg = ($arg) ? 'true' : 'false';
                $format = '<span class="__BtrD__char-bool">' . $arg . '</span>';
            }
            elseif ($type == 'double')
            {
                $format = '<span class="__BtrD__char-double">' . $arg . '</span>';
            }
            elseif ($type == 'NULL')
            {
                $format = '<span class="__BtrD__char-null">null</span>';
            }
            elseif ($type == 'float')
            {
                $format = '<span class="__BtrD__char-float">' . $arg . '</span>';
            }
            elseif ($type == 'array')
            {
                $format .= '[  <span class="__BtrD__caret"></span>  <div class="__BtrD__env-arr">';

                foreach ($arg as $key => $value)
                {
                    $key = str_replace('<', '&lt;', $key);
                    if (is_array($value))
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

    /**
     * @return string
     */
    private function top(): string
    {
        return '<div class="__BtrD__logo __BtrD__tops">
            <span class="__BtrD__logo-img"></span>
            <span class="__BtrD__theme">Theme: ' . $this->theme . '</span>
        </div>
        <div class="__BtrD__hints __BtrD__tops">
            <div class="__BtrD__type __BtrD__type-object">OBJECT</div>
            <div class="__BtrD__type __BtrD__type-null">NULL</div>
            <div class="__BtrD__type __BtrD__type-bool">BOOL</div>
            <div class="__BtrD__type __BtrD__type-array">ARRAY</div>
            <div class="__BtrD__type __BtrD__type-float">FLOAT</div>
            <div class="__BtrD__type __BtrD__type-double">DOUBLE</div>
            <div class="__BtrD__type __BtrD__type-string">STRING</div>
            <div class="__BtrD__type __BtrD__type-integer">INTEGER</div>
        </div>
        ';
    }

    /**
     * @param string $file
     * @param string $line
     * @param int $code
     * @param array $trace
     * @return string
     */
    private function left(string $file, string $line, string $code, array $trace = [])
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
            if (! isset($traces['line']))
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
            $traced .= '<div class="__BtrD__loop-tog __BtrD__l-parent" data-id="proc-' . $i . '" ' . $dsc . '>
                            <div class="__BtrD__id __BtrD__loop-tog __BtrD__code">' . $i . '</div>
                            <div class="__BtrD__holder">
                                <span class="__BtrD__name">'. $class . '<b>' . $type .
                                '</b>' . $function . '<i class="__BtrD__line">' . $_line . '</i></span>
                                <span class="__BtrD__path">' . $_file . '</span> 
                            </div>   
                        </div>';


            $micro_time = microtime(true) - $start;

            $memory .= '<div class="__BtrD__memory __BtrD__loop-tog __BtrD__l-parent" data-id="proc-' . $i . '" ' . $dsc . '>
                            <div class="__BtrD__id __BtrD__loop-tog __BtrD__code">' . $i . '</div>
                            <div class="__BtrD__holder">
                                <span class="__BtrD__name">' . memory_get_usage() . '</span>
                                <span class="__BtrD__path"> ' .$micro_time . '</span> 
                            </div>
                       </div>';

            $_code = rtrim($this->chunk($_file, ($_line != '') ? (int) $_line : 0));

            $this->contents[] = '<div class="__BtrD__code-view" id="proc-' . $i . '" style="display:none;">' . Highlight::render($_code) . '</div>';
        }
        
        $margin = strlen($code) * 3;
        return '<div class="__BtrD__content-nav" id="cont-nav">
                    <div class="__BtrD__top-tog __BtrD__active" id="__BtrD__location">Location</div> 
                    <div class="__BtrD__top-tog" id="__BtrD__function">Trace</div> 
                    <div class="__BtrD__top-tog" id="__BtrD__memory">Memory</div> 
                </div>
                <div class="__BtrD__content-body">
                    <div class="__BtrD__location __BtrD__loops __BtrD__active">
                        <div class="__BtrD__loop-tog __BtrD__l-parent" data-id="proc-main" data-line="' . $line . '" data-file="' . $file . '">
                            <div class="__BtrD__id __BtrD__loop-tog __BtrD__code">' . $code . '</div>
                            <div class="__BtrD__holder">
                                <span class="__BtrD__name" style="margin-left:' . $margin . 'px">' . $file_name . '<i class="line">' . $line . '</i> </span>
                                <span class="__BtrD__path">' . $file_path . '</span>             
                            </div>   
                        </div>
                        <div class="__BtrD__loop-tog __BtrD__l-parent" data-id="proc-buffer">
                            <div class="__BtrD__holder">
                                <span class="__BtrD__name" style="padding-left: 0px;">Output Buffer</span>
                                <span class="__BtrD__path">Toggle contents sent to output buffer</span>             
                            </div>   
                        </div>
                    </div>
                    <div class="__BtrD__function __BtrD__loops">' . $traced . '</div><div class="__BtrD__memory __BtrD__loops">' .  $memory . '</div>
                </div> ';
    }

    /**
     * @param string $type
     * @param string $message
     * @param string $file
     * @param int $line
     * @return string
     */
    private function middle(string $type, string $message, string $file, int $line): string
    {
        $code = rtrim($this->chunk($file, $line));
        $output = base64_encode(ob_get_clean() ?: '');
        if ($output == '')
        {
            $output = '<h3 style="text-align: center;">No output sent to buffer</h3>';
        }

        $message = (strlen($message) > 0) ? $message : 'No message passed in ' . $type . ' construct';

        $q_str = str_replace('"', '', $message);
        $g = 'php ' . $type . ' ' . $q_str;
        $s = '[php] ' . $q_str;
        return '<div class="__BtrD__exception-type">
                    <span>' . $type . '</span>
                    <div class="__BtrD__action">
                        <span title="lookup error message in stackoveflow" onclick="window.open(\'http://stackoverflow.com/search?q=' . $s . '\', \'_blank\')"><span class="__BtrD__caret"></span> stackoverflow</span>
                        <span title="lookup error message in google" onclick="window.open(\'https://www.google.com/search?q=' . $g . '\', \'_blank\')"><span class="__BtrD__caret"></span> google</span>
                    </div>
                </div>
                <div class="__BtrD__exception-msg">' . $this->highlight($message) . '</div>
                <div class="__BtrD__code-view" id="proc-main">' . Highlight::render($code) . '</div>
                <div class="__BtrD__browser-view" id="proc-buffer" style="display:none">' . $output . '</div>' . implode($this->contents) . '
                <div class="__BtrD__active-desc" id="repop">
                    <div class="__BtrD__keyword">Class: <span class="__BtrD__char-null">null</span></div>
                    <div class="__BtrD__namespace">Namespace: <span class="__BtrD__char-null">null</span></div>
                    <div class="__BtrD__file">File: ' . $file . ':<span class="__BtrD__char-integer">' . $line . '</span></div>
                    
                </div>';
    }

    /**
     * @return string
     */
    private function right(): string
    {
        $globals = [
            'Server' => $_SERVER ?? [],
            'Get' => $_GET ?? [],
            'Post' => $_POST ?? [],
            'Files' => $_FILES ?? [],
            'Request' => $_REQUEST ?? [],
            'Session' => $_SESSION ?? [],
            'Cookie' => $_COOKIE ?? [],
            'Env' => $_ENV ?? []
        ];

        $side = '';
        $count = 1;
        foreach ($globals as $names => $attributes)
        {
            $side .= '<div class="__BtrD__global">
                    <div class="__BtrD__labeled" id="tog-' . $count . '"><span class="__BtrD__caret"></span> &nbsp;&nbsp; ' . $names . '</div>
                    <div class="__BtrD__content" style="display:none;">' . PHP_EOL;
            foreach ($attributes as $key => $values)
            {
                $side .= '<div class="__BtrD__listed">
                            <span class="__BtrD__index">' . $key . '</span> :
                            <span class="__BtrD__value">' . $this->get($values) . '</span>
                        </div>';
            }
            $side .= '</div></div>';
            $count++;
        }
        return $side;
    }

    /**
     * @param string $content
     * @return string
     */
    private function html(string $content): string
    {
        $DIRS = DIRECTORY_SEPARATOR;
        $theme_file = __DIR__ . $DIRS . 'Assets' . $DIRS;
        $theme = file_get_contents($theme_file . $this->theme . '.css');
        $image = base64_encode(@file_get_contents($theme_file . $this->theme . '.png') ?: '');
        $font_bld = base64_encode(file_get_contents($theme_file . 'Fonts' . $DIRS . 'Inconsolata.woff2'));

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bittr Debug</title>
        <style>
        @font-face{font-family:Inconsolata;src:url(data:font/truetype;charset=utf-8;base64,' . $font_bld . ') format("woff2");}
        .__BtrD__middle,body{overflow:hidden!important}.__BtrD__contents .__BtrD__right .__BtrD__global,.__BtrD__left .__BtrD__content-body .__BtrD__loops .__BtrD__holder:hover,.__BtrD__left .__BtrD__content-nav .__BtrD__top-tog{cursor:pointer}.__BtrD__contents .__BtrD__listed:last-child,.__BtrD__contents .__BtrD__right .__BtrD__global:last-child{border-bottom:none}html{font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-size:10px;-webkit-tap-highlight-color:transparent}body{margin:0!important}.__BtrD__container,.__BtrD__container-fluid{margin-right:auto;margin-left:auto;padding-right:15px;padding-left:15px}.__BtrD__caret,.__BtrD__header .__BtrD__hints .__BtrD__type,.__BtrD__left .__BtrD__content-nav .__BtrD__top-tog{display:inline-block}*,:after,:before{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}@media (min-width:768px){.__BtrD__container{width:750px}}@media (min-width:992px){.__BtrD__container{width:970px}}@media (min-width:1200px){.__BtrD__container{width:1170px}}.__BtrD__row{margin-right:-15px;margin-left:-15px}.__BtrD__col-md-3,.__BtrD__col-md-6{position:relative;min-height:1px;padding-right:15px;padding-left:15px}@media (min-width:992px){.__BtrD__col-md-3,.__BtrD__col-md-6{float:left}.__BtrD__col-md-6{width:50%}.__BtrD__col-md-3{width:25%}}.__BtrD__caret{width:0;height:0;margin-left:2px;vertical-align:middle;border-top:4px dashed;border-top:4px solid\9;border-right:4px solid transparent;border-left:4px solid transparent}.__BtrD__header{padding:5px;height:35px;width:100%}.__BtrD__header .__BtrD__hints{position:absolute;right:0}.__BtrD__header .__BtrD__type{margin-left:10px;width:70px;text-align:center;border-radius:10px;font-size:10px;margin-top:5px}.__BtrD__header .__BtrD__logo span,.__BtrD__header .__BtrD__tops{display:inline-block;margin-left:20px;vertical-align:middle}.__BtrD__header .__BtrD__logo-img{width:80px;height:30px;margin-top:-2px}.__BtrD__header .__BtrD__logo span button,.__BtrD__header .__BtrD__logo span button:active,.__BtrD__header .__BtrD__logo span button:active:hover{border:none}.__BtrD__header .__BtrD__logo span .__BtrD__dropdown-menu li a{padding:8px 30px}.__BtrD__header .__BtrD__logo span .__BtrD__dropdown-menu .__BtrD__divider{margin:0}.__BtrD__contents .__BtrD__attr{height:96vh;padding:0!important}.__BtrD__contents .__BtrD__right{word-wrap:break-word;font-weight:600!important}.__BtrD__left .__BtrD__content-nav .__BtrD__top-tog{font-weight:600;width:32%;text-align:center;margin-left:-8px;padding-top:5px;font-size:12px;padding-bottom:5px;text-transform:uppercase}.__BtrD__left .__BtrD__content-nav .__BtrD__top-tog:first-child{padding-left:10px;border-left:none;margin-right:-1px}.__BtrD__left .__BtrD__content-nav .__BtrD__top-tog:last-child{margin-left:-7px;border-right:none}.__BtrD__left .__BtrD__content-body{height:95%}.__BtrD__l-parent{margin-left:3px;margin-top:1px}.__BtrD__left .__BtrD__content-body .__BtrD__loops{margin-top:3px;width:100%;display:none}.__BtrD__left .__BtrD__content-body .__BtrD__loops .__BtrD__holder i{float:right;margin-top:10px;font-size:10px;border-radius:10px;padding:1px 10px;font-style:normal}.__BtrD__left .__BtrD__content-body .__BtrD__loops .__BtrD__holder{padding:5px 10px}.__BtrD__left .__BtrD__content-body .__BtrD__loops div span{display:block}.__BtrD__left .__BtrD__content-body .__BtrD__loops div .__BtrD__name{font-weight:600;padding-left:10px}.__BtrD__left .__BtrD__content-body .__BtrD__loops div .__BtrD__path{font-size:11px}.__BtrD__left .__BtrD__content-body .__BtrD__loops .__BtrD__id{font-size:10px;text-align:center;border:none;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;position:absolute;left:4px;padding:4px 3px 4px 1px;display:inline-block;vertical-align:middle}.__BtrD__left .__BtrD__content-body .__BtrD__loops.__BtrD__active{display:block}.__BtrD__exception-type{padding:10px}.__BtrD__exception-type>.__BtrD__action,.__BtrD__exception-type>span{display:inline-block}.__BtrD__exception-type>.__BtrD__action{float:right}.__BtrD__exception-type>.__BtrD__action span{cursor:pointer;font-size:12px;padding:0 10px 2px;border-radius:10px}.__BtrD__exception-type>span{border-radius:10px;padding:3px 10px}.__BtrD__exception-msg{padding:10px;font-size:13px}.__BtrD__active-desc{position:absolute;width:100%;bottom:0;padding:10px;font-size:12px;z-index:4!important}.__BtrD__active-desc .__BtrD__file b{font-size:10px;margin-top:-5px}.__BtrD__browser-view{width:103%}.__BtrD__code-view table{font-size:12px;font-weight:400;width:100%;white-space:nowrap;table-layout:fixed;border-collapse:collapse}.__BtrD__code-view table .function,.__BtrD__code-view table .keyword{font-weight:600}.__BtrD__code-view table tr td:first-child{width:60px;text-align:center}.__BtrD__code-view table tr .__BtrD__line-content{padding-left:5px}.__BtrD__code-view table .__BtrD__highlighted td{padding:8px 0!important}.__BtrD__code-view table .__BtrD__highlighted td:first-child{border:none;font-weight:700}.__BtrD__contents .__BtrD__right .__BtrD__global:first-child{border-top:none}.__BtrD__contents .__BtrD__right .__BtrD__global .__BtrD__env-arr{padding-left:20px}.__BtrD__contents .__BtrD__right .__BtrD__global .__BtrD__content{cursor:default}.__BtrD__contents .__BtrD__right .__BtrD__global .__BtrD__content .__BtrD__caret{cursor:pointer}.__BtrD__contents .__BtrD__right .__BtrD__global .__BtrD__content,.__BtrD__contents .__BtrD__right .__BtrD__global .__BtrD__labeled{display:block}.__BtrD__contents .__BtrD__right .__BtrD__global .__BtrD__labeled{font-weight:600;padding:10px}.__BtrD__contents .__BtrD__listed{padding:5px 14px 5px 10px}.__BtrD__contents .__BtrD__right .__BtrD__global .__BtrD__content{padding:5px 0;width:104%}.__BtrD__contents .__BtrD__listed .__BtrD__index,.__BtrD__contents .__BtrD__listed .__BtrD__value{font-weight:500;font-size:10px}.__BtrD__ss-wrapper{overflow:hidden;width:100%;height:100%;position:relative;float:left}.__BtrD__ss-content{height:100%;width:104%;position:relative;overflow:auto;box-sizing:border-box}.__BtrD__ss-scroll{position:relative;width:6px;border-radius:4px;top:0;z-index:2;cursor:pointer;opacity:0;transition:opacity .25s linear}.__BtrD__ss-hidden{display:none}.__BtrD__ss-container:hover .__BtrD__ss-scroll{opacity:1}.__BtrD__ss-grabbed{-o-user-select:none;-ms-user-select:none;-moz-user-select:none;-webkit-user-select:none;user-select:none}
        ' . $theme . '
        .__BtrD__header .__BtrD__logo .__BtrD__logo-img {background: url(data:image/png;base64,' . $image . ') no-repeat;background-size: contain;}
    </style>
    </head>
    <body>
    ' . $content . '
    <script>' . file_get_contents($theme_file . 'min.js') . '</script>
    </body>
</html>';
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return array
     */
    public function getTrace(): array
    {
        return $this->trace;
    }

    /**
     * @return string
     */
    public function getTraceAsString(): string
    {
        return $this->trace_string;
    }
}
