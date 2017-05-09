<?php

namespace Debug;


class Type
{
    private static function objects($objects)
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
                $format .= '<span class="public">public&nbsp;&nbsp;&nbsp; </span> : ';
            }

            $prop->setAccessible(true);
            $format .= self::get($prop->getValue($objects)) . '; <br />';
        }

        $temp .= '<span class="char-object">' . $obj->getName() . '</span> [  <span class="caret"></span>  <div class="env-arr">';

        $temp .= $format . '</div>]';
        return $temp;
    }

    public static function get($arguments, $array_loop = false)
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
                        $format .= '<span class="key">' . $key . '</span> : ' . self::get($value, true) . ',<br />';
                    }
                    else
                    {
                        $format .= '<span class="key">' . $key . '</span> : ' . self::get($value, true) . ',<br/>';
                    }
                }

                $format .= '</div>]';
            }
            elseif ($type == 'object')
            {
                $format .= self::objects($arg);
            }
        }

        return $format;
    }
}