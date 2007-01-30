<?php
// Creates a test skeleton for all public methods in a class.
function skeleton($class, $methods) {
    $text[] = "<?php\n";
    $text[] = "class Test_$class extends Solar_Test {";
    $text[] = "    ";
    $text[] = "    public function __construct(\$config = null)";
    $text[] = "    {";
    $text[] = "        parent::__construct(\$config);";
    $text[] = "    }";
    $text[] = "    ";
    $text[] = "    public function __destruct()";
    $text[] = "    {";
    $text[] = "        parent::__destruct();";
    $text[] = "    }";
    $text[] = "    ";
    $text[] = "    public function setup()";
    $text[] = "    {";
    $text[] = "        parent::setup();";
    $text[] = "    }";
    $text[] = "    ";
    $text[] = "    public function teardown()";
    $text[] = "    {";
    $text[] = "        parent::teardown();";
    $text[] = "    }";
    
    foreach ($methods as $method) {
        $method = ucfirst($method);
        $text[] = "    ";
        $text[] = "    public function test$method()";
        $text[] = "    {";
        $text[] = "        \$this->todo('incomplete');";
        $text[] = "    }";
    }
    
    $text[] = '}';
    $text[] = '?>';
    return implode("\n", $text);
}


// makes a set of placeholders for testing a class
if (! isset($argv[1])) {
    die("STOP: Please specify the class to create tests for.\n");
}

// ---------------------------------------------------------------------

// start Solar
error_reporting(E_STRICT | E_ALL);
require_once 'Solar.php';
Solar::start(false);

// where are the tests?
$dir = dirname(__FILE__) . '/Test/';

// what class are we creating tests for?
$class = $argv[1];
Solar::loadClass($class);

// does the test file exist? (don't want to overwrite).
$sub = str_replace('_', DIRECTORY_SEPARATOR, $class);
$file = "$dir$sub.php";
if (is_readable($file)) {
    die("STOP: Test already exists ($file).");
}

if (! is_dir(dirname($file))) {
    mkdir(dirname($file), 0755, true);
}
// don't create tests for these public methods, they're from Solar_Base
$ignore = array('apiVersion', 'locale');

// get the list of public methods to create tests for
$reflect = new ReflectionClass($class);
$methods = array();
foreach ($reflect->getMethods() as $method) {
    $public = $method->isPublic();
    $name = $method->getName();
    if ($public && ! in_array($name, $ignore)) {
        $methods[] = $name;
    }
}

$text = skeleton($class, $methods);
file_put_contents($file, $text);

// done!
Solar::stop();
?>