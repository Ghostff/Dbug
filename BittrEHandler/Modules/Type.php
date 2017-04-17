<?php
/**
 * Created by PhpStorm.
 * User: Chrys
 * Date: 4/17/2017
 * Time: 3:44 PM
 */

namespace BittrEHandler\Modules;


class Type
{

    /**
     * @var string - null data type
     */
    private static $_null = '6789f8';
    /**
     * @var string - variable type
     */
    private static $_type = 'AAAAAA';
    /**
     * @var string - bool data type
     */
    private static $_bool = 'f8b93c';
    /**
     * @var string - array data type
     */
    private static $_array	= '6db679';
    /**
     * @var string - float data type
     */
    private static $_float = '9C6E25';
    /**
     * @var string - double data type
     */
    private static $_double = 'a66b47';
    /**
     * @var string - string data  type
     */
    private static $_string = 'ff9999';
    /**
     * @var string - length of any data value
     */
    private static $_lenght = '5BA415';
    /**
     * @var string - int data type
     */
    private static $_integer = '1BAABB';
    /**
     * @var string - object data type
     */
    private static $_object = '000000';
    /**
     * @var string - object properties visibility
     */
    private static $_vsble = '741515';
    /**
     * @var string - object name
     */
    private static $_object_name = '5ba415';
    /**
     * @var string - object property name
     */
    private static $_obj_prop_name = '987a00';
    /**
     * @var string - object property name and value separator
     */
    private static $_obj_prop_acc = 'f00000';
    /**
     * @var string - array of array key
     */
    private static $_parent_arr = '59829e';
    /**
     * @var string - array of array accessor symbol
     */
    private static $_parent_arr_acc = 'e103c4';
    /**
     * @var string - array
     */
    private static $_child_arr = 'f07b06';
    /**
     * @var string - array value accessor symbol
     */
    private static $_child_arr_acc = 'f00000';
    
    public static function get($arg)
    {
        $type = gettype($arg);
        if ($type == 'string')
        {
            $arg =  str_replace('<', '&lt;', $arg);
            $format = '<span class="string" style="color:#' . self::$_string . '">' . $arg . '</span>';
        }
        elseif ($type == 'integer')
        {
            $format = '<span class="integer" style="color:#' . self::$_integer . '">' . $arg . '</span>';
        }
        elseif ($type == 'boolean')
        {
            $arg = ($arg) ? 'true' : 'false';
            $format = '<span class="bool" style="color:#' . self::$_bool . '">' . $arg . '</span>';
        }
        elseif ($type == 'double')
        {
            $format = '<span class="double" style="color:#' . self::$_double . '">' . $arg . '</span>';
        }
        elseif ($type == 'NULL')
        {
            $format = '<span class="null" style="color:#' . self::$_null . '">null</span>';
        }
        elseif ($type == 'float')
        {
            $format = '<span class="float" style="color:#' . self::$_float . '">' . $arg . '</span>';
        }
        elseif ($type == 'array')
        {
        }

        return $format;
    }
}