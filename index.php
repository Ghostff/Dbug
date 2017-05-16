<?php



spl_autoload_register(function ($name) {
    include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php'; #include str_replace(['\\', 'Debug'], [DIRECTORY_SEPARATOR, 'src'], $name) . '.php';

});

new Debug\BittrDbug(\Debug\BittrDbug::PRETTIFY, 'yola', 45);

echo 111;
class Foo
{
    private $string = 'string';
    protected $int = 20;
    public $array = [
        'foo'   => 'bar'
    ];
    protected static $bool = false;
}
$_POST = ['foo' => 22, 'bar' => new Foo()];

function renderError()
{
    trigger_error("Cannot divide by zero", E_USER_ERROR);
}
function duplicateKey()
{
    renderError();
}




var_dump(new Foo());
#duplicateKey();;
\BittrEHandler\Modules\Dump::error();



class Enum {
    protected $self = array();
    public function __construct( /*...*/ ) {
        $args = func_get_args();
        for( $i=0, $n=count($args); $i<$n; $i++ )
            $this->add($args[$i]);
    }

    public function __get( /*string*/ $name = null ) {
        return $this->self[$name];
    }

    public function add( /*string*/ $name = null, /*int*/ $enum = null ) {
        if( isset($enum) )
            $this->self[$name] = $enum;
        else
            $this->self[$name] = end($this->self) + 1;
    }
}

class DefinedEnum extends Enum {
    public function __construct( /*array*/ $itms ) {
        foreach( $itms as $name => $enum )
            $this->add($name, $enum);
    }
}


