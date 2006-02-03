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
--FILE---
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

define('SOLAR_CONFIG_PATH', dirname(__FILE__) . '/_config.inc');
require_once 'Solar.php';
Solar::start();

// what class are we creating tests for?
$class = $argv[1];

// does the test directory exist?
// (don't want to overwrite).
if (is_dir($class)) {
    die("STOP: Directory for '$class' already exists.\n");
}

// make the test directory, but only after the class-load succeeds.
Solar::loadClass($class);
mkdir($class);

// don't create tests for these methods, they're from Solar_Base
$base = array('apiVersion', 'locale', 'solar');

// get the list of public methods to create test files for
$reflect = new ReflectionClass($class);
foreach ($reflect->getMethods() as $method) {
    $public = $method->isPublic();
    $name = $method->getName();
    if ($public && ! in_array($name, $base)) {
        $test = skeleton($class, $name);
        file_put_contents("$class/$name.phpt", $test);
        echo "$class/$name.phpt\n";
    }
}

// done!
Solar::stop();
?>