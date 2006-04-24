<?php
/**
 * Creates a test skeleton for all public methods in a class.
 */
 
/**
 * Creates a skeleton test script for a class method.
 */
function skeleton($class, $method) {
return <<<END
--TEST--
$class::$method()
--SKIPIF--
<?php echo 'skip test incomplete' ?>
--FILE--
<?php
// include ../_prepend.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_prepend.inc')) {
    require dirname(dirname(__FILE__)) . '/_prepend.inc';
}

// include ./_prepend.inc
if (is_readable(dirname(__FILE__) . '/_prepend.inc')) {
    require dirname(__FILE__) . '/_prepend.inc';
}

// ---------------------------------------------------------------------


// ---------------------------------------------------------------------

// include ./_append.inc
if (is_readable(dirname(__FILE__) . '/_append.inc')) {
    require dirname(__FILE__) . '/_append.inc';
}
// include ../_append.inc
if (is_readable(dirname(dirname(__FILE__)) . '/_append.inc')) {
    require dirname(dirname(__FILE__)) . '/_append.inc';
}
?>
--EXPECT--

END;
}

// ---------------------------------------------------------------------

// makes a set of placeholders for testing a class
if (! isset($argv[1])) {
    die("STOP: Please specify the class to create a test directory for.\n");
}

$dir = dirname(__FILE__);
define('SOLAR_CONFIG_PATH', "$dir/_config.inc");
require_once 'Solar.php';
Solar::start();

// what class are we creating tests for?
$class = $argv[1];
Solar::loadClass($class);

// does the test directory exist?
// (don't want to overwrite).
if (! is_dir($class)) {
    mkdir($class);
}

// don't create tests for these methods, they're from Solar_Base
$base = array('apiVersion', 'locale');

// get the list of public methods to create test files for
$reflect = new ReflectionClass($class);
foreach ($reflect->getMethods() as $method) {
    $public = $method->isPublic();
    $name = $method->getName();
    $file = "$class/$name.phpt";
    if ($public && ! in_array($name, $base) && ! file_exists("$dir/$file")) {
        $test = skeleton($class, $name);
        file_put_contents($file, $test);
        echo "$file\n";
    }
}

// done!
Solar::stop();
?>