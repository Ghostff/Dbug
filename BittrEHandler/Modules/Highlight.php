<?php

namespace BittrEHandler\Modules;


class Highlight
{
    private static $styled = '.line-number{display:none;}';
    private static $cache_path = null;
    public static $highlight_line = 0;

    private static $cast = 'F25959';
    private static $null = '989898';
    private static $bool = 'f2368a';
    private static $self = '1D6F0C';
    private static $quote = '13ab7b';
    private static $parent = '1D6F0C';
    private static $number = 'A4AC21';
    private static $comment = 'FEA500';
    private static $tag_open = 'F00000';
    private static $keywords = '13ab7b';
    private static $function = '13ab7b';
    private static $variable = '409CC8';
    private static $constant = '';
    private static $tag_close = 'F00000';
    private static $operators = '';
    private static $semi_colon = '';
    private static $parenthesis = '';
    private static $return_type = 'f2368a';
    private static $php_function = '6367A7';
    private static $curly_braces = '';
    private static $parameter_type = 'f2368a';
    private static $square_bracket = '';
    private static $custom_function = '9313A4';
    private static $multi_line_comment = 'FEA500';



    private static $self_ptrn = '/(?<!\$|\w)self/';
    private static $cast_ptrn = '/(\(\s*(int|string|float|array|object|unset|binary|bool)\s*\))/';
    private static $bool_ptrn = '/\b(?<!\$)true|false/i';
    private static $null_ptrn = '/\b(?<!\$)(null)\b/';
    private static $quote_ptrn = '/((?<!\\\)\'(.*?)(?<!\\\)\'|
        (?<!((style|class|label)=)|(\\\))"(?!\s(class|label)=|>).*?(?<!((style|class|label)=)|(\\\))"(?!\s(class|label)=|>))/s';
    private static $parent_ptrn = '/(?<!\$|\w)parent\b/';
    private static $number_ptrn = '/\b(\d+)\b/';
    private static $comment_ptrn = '/(?<!(http(s):))\/\/.*|(?<!color:)#.*/';
    private static $variable_ptrn = '/\$(\$*)[a-zA-Z_]+[a-zA-Z0-9_]*/';
    private static $function_ptrn = '/(?<=\s|^)(function)(?=\s)/';
    private static $constant_ptrn = '/\b(?<!(\#|\$))([A-Z_]+)(?!<\/\w+>\()\b/';
    private static $keywords_ptrn = '/(?<!\$|\w)((a(bstract|nd|rray\s*(?=\()|s))|
        (c(a(llable|se|tch)|l(ass(?!=)|one)|on(st|tinue)))|
        (d(e(clare|fault)|ie|o))|
        (e(cho|lse(if)?|mpty|nd(declare|for(each)?|if|switch|while)|val|x(it|tends)))|
        (f(inal|or(each)?))|
        (g(lobal|oto))|
        (i(f|mplements|n(clude(_once)?|st(anceof|eadof)|terface)|sset))|
        (n(amespace|ew))|
        (p(r(i(nt|vate)|otected)|ublic))|
        (re(quire(_once)?|turn))|
        (s(tatic|witch))|
        (t(hrow|r(ait|y)))|
        (u(nset(?!\s*\))|se))|
        (__halt_compiler|break|list|(x)?or|var|while))\b/';
    private static $operators_ptrn = '/(\=|\.|\!|\+|\%|\-|(?<!https|http)\:|\@|\||\?|&gt;|&lt;|&amp;)/';
    private static $semi_colon_ptrn = '/(?<![&lt|&gt|&amp]);/';
    private static $parenthesis_ptrn = '/\(|\)/';
    private static $return_type_ptrn = '/(?<=\:\<\/span\>)\s*(?:\<\w+ \w+="\w+:#\w+" \w+="\w+"\>\?\<\/\w+\>)*(string|bool|array|float|int|callable|void)/';
    private static $curly_braces_ptrn = '/\{|\}/';
    private static $parameter_type_ptrn = '/(?<!\w)(string|bool|array|float|int|callable)\s*(?=\<\w+ \w+="\w+:#\w+" \w+="\w+"\>\$)/';
    private static $square_bracket_ptrn = '/\[|\]/';
    private static $multi_line_comment_ptrn = '/\/\*(.*?)\*\//';



    /**
     * updates attributes of class property
     *
     * @param string $property
     * @param string $values
     * @return void
     */
    public static function set(string $property, string $values): void
    {
        if (property_exists(__CLASS__, $property)) {
            self::${$property} = $values;
        } else {
            throw new RuntimeException(sprintf('%s does not exist in %s', $property, __CLASS__));
        }
    }


    /**
     * adds code to a span tag
     *
     * @param string $color
     * @param string $class
     * @param string $content
     * @return string
     */
    private static function span(string $color, string $class, string $content = '$0'): string
    {
        $span = sprintf('<span style="color:#%s" class="%s">%s</span>', $color, $class, $content);
        return $span;
    }


    /**
     * adds code to a font tag
     *
     * @param string $color
     * @param string $class
     * @param string $content
     * @return string
     */
    private static function font(string $color, string $class, string $content = '$0'): string
    {
        $font = sprintf('<font style="color:#%s" class="%s">%s</font>', $color, $class, $content);
        return $font;
    }

    /**
     * php preg replace function
     *
     * @param array $pattern
     * @param array $replacement
     * @param string $subject
     * @return string
     */
    private static function PR(array $pattern, array $replacement, string $subject): string
    {
        return preg_replace($pattern, $replacement, $subject);
    }


    /**
     * check and highlight user defined  or php pre defined function
     *
     * @param string $code
     * @return string
     */
    private static function isFunction(string $code): string
    {
        return preg_replace_callback('/(\w+)(?=\s\(|\()/', function ($arg)
        {
            $func = $arg[1];
            if (function_exists($func)) {
                return self::span(self::$php_function, 'php_function', $func);
            }
            else {
                return self::span(self::$custom_function, 'custom_function', $func);
            }
        }, $code);
    }


    /**
     * Highlights strings
     *
     * @param string $code
     * @param string $file_name
     * @param bool $cache
     * @param bool $tabs_to_space
     * @return string
     */
    private static function format(string $code, string $file_name, bool $cache , bool $tabs_to_space): string
    {
        #throw new \Exception("Error Processing Request", 1);
        $code = str_replace(
            array('<?php', '<?=', '?>', '\\\\'),
            array('PP_PHP_LONG_TAG_OPEN', 'PP_PHP_SHORT_TAG_OPEN', 'PP_PHP_CLOSE_TAG', 'PP_PHP_DOUBLE_BACK_SLASH'),
            $code
        );

        $code = htmlspecialchars($code, ENT_NOQUOTES);
        $new_code = null;
        foreach (preg_split('/\n/', $code)  as $line_number => $lines)
        {
            if (trim($lines) == false) {
                $lines = ' ';
            }
            $line_number++;
            $HT = (self::$highlight_line == $line_number) ? ' class="highlighted"' : '';
            $new_code .= '<tr' . $HT . '><td class="line-number">' . $line_number . '</td><td class="line-content">';

            $pattern = array(
                self::$operators_ptrn,
                self::$number_ptrn,
                trim(preg_replace('/\s\s+/', '', self::$keywords_ptrn)),
                self::$function_ptrn,
                self::$variable_ptrn,
                self::$cast_ptrn
            );

            $replacement = array(
                self::span(self::$operators, 'operators'),
                self::span(self::$number, 'number'),
                self::span(self::$keywords, 'keyword'),
                self::span(self::$function, 'function', '$1'),
                self::span(self::$variable, 'variable'),
                self::span(self::$cast, 'cast')
            );

            $new_line = self::PR($pattern, $replacement, $lines);

            $new_line = self::isFunction($new_line);

            $pattern = array(
                self::$constant_ptrn,
                self::$parenthesis_ptrn,
                self::$curly_braces_ptrn,
                self::$square_bracket_ptrn,
                self::$null_ptrn,
                self::$self_ptrn,
                self::$parent_ptrn,
                self::$bool_ptrn,
                self::$comment_ptrn,
                self::$parameter_type_ptrn,
                self::$return_type_ptrn,
                self::$semi_colon_ptrn,
                '/PP_PHP_LONG_TAG_OPEN/',
                '/PP_PHP_SHORT_TAG_OPEN/',
                '/PP_PHP_CLOSE_TAG/',
                '/PP_PHP_DOUBLE_BACK_SLASH/'
            );

            $replacement = array(
                self::span(self::$constant, 'constant'),
                self::span(self::$parenthesis, 'parenthesis'),
                self::span(self::$curly_braces, 'curly_braces'),
                self::span(self::$square_bracket, 'square_bracket'),
                self::span(self::$null, 'null'),
                self::span(self::$self, 'self'),
                self::span(self::$parent, 'parent'),
                self::span(self::$bool, 'bool'),
                self::span(self::$comment, 'strip comment'),
                self::span(self::$parameter_type, 'parameter_type'),
                self::span(self::$return_type, 'return_type'),
                self::span(self::$semi_colon, 'semi_colon'),
                self::span(self::$tag_open, 'tag long', '&lt;?php'),
                self::span(self::$tag_open, 'tag short', '&lt;?='),
                self::span(self::$tag_close, 'tag clode', '?>'),
                '\\\\\\'
            );
            $new_code .= self::PR($pattern, $replacement, $new_line) . '</td></tr>';

        }

        $pattern = array(
            self::$multi_line_comment_ptrn,
            trim(preg_replace('/\s\s+/', '', self::$quote_ptrn))
        );

        $replacement = array(
            self::font(self::$multi_line_comment, 'strip multi_line_comment', '$0'),
            self::font(self::$quote, 'strip quote', '$0')
        );

        $new_code = self::PR($pattern, $replacement, $new_code);
        $new_code = str_replace(array('\"', '\\\''), array('"', '\''), $new_code);

        if ($tabs_to_space) {
            $new_code = preg_replace('/\t/', '&nbsp;&nbsp;&nbsp;&nbsp;', $new_code);
        }

        $style = '.strip font,.strip span{color:inherit !important}' . self::$styled;
        $pretty = '<table>'. $new_code . '</table><style>' . $style . '</style>';

        if ($cache) {
            $cache_name = self::$cache_path . DIRECTORY_SEPARATOR . intval($file_name, 36);
            @file_put_contents($cache_name, $pretty);
            self::cache($file_name, false);
        }
        return $pretty;
    }


    /**
     * caches formatted strings and handles gc. (currently available for file highlight alone)
     *
     * @param string $name
     * @param bool $is_get
     * @return string
     */
    private static function cache(string $name, bool $is_get = true): string
    {
        $file = self::$cache_path . DIRECTORY_SEPARATOR . '_x86';
        if ($is_get)
        {
            @$content = file_get_contents($file);
            $content = (array) json_decode($content);
            if ((isset($content[$name])) && ($content[$name] == filemtime($name))) {
                return @file_get_contents(self::$cache_path . DIRECTORY_SEPARATOR . intval($name, 36));
            }
        }
        else
        {
            if ( ! is_dir(self::$cache_path)) {
                mkdir(self::$cache_path);
                file_put_contents($file, '');
            }
            @$content = file_get_contents($file);
            $content = (array) json_decode($content);
            if (( ! isset($content[$name])) || ((isset($content[$name])) && ($content[$name] != filemtime($name)))) {
                $content[$name] = filemtime($name);
                file_put_contents($file, json_encode($content));
            }
        }
        return '';

    }


    /**
     * check if code is a file or a string then renders accordingly
     *
     * @param string $code
     * @param bool $is_file
     * @param bool $cache
     * @param bool $tabs_to_space
     * @return string
     */
    public static function render(string $code, bool $is_file = false, bool $cache = true, bool $tabs_to_space = true): string
    {
        self::$cache_path = __DIR__ . DIRECTORY_SEPARATOR . '.caches';
        if ($is_file) {
            if ($cache) {
                $cached = self::cache($code);
                if ($cached != '') {
                    return $cached;
                }
            }
            return self::format(file_get_contents($code), $code, $cache, $tabs_to_space);
        }
        return self::format($code, '', false, true);
    }


    /**
     * sets formatted string out layer style
     */
    public static function numberLines(): void
    {
        self::$styled = '';
    }
}