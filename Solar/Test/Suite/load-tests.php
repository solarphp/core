<?php
function __autoload($class) {
    $class_file = str_replace('_', DIRECTORY_SEPARATOR, $class) . ".php";
    require_once $class_file;
}

function solar_load_test_files($dir)
{
    $list = glob($dir . DIRECTORY_SEPARATOR . "[A-Z]*.php");
    foreach ($list as $class_file) {
        require_once $class_file;
    }
    
    $list = glob($dir . DIRECTORY_SEPARATOR . "[A-Z]*", GLOB_ONLYDIR);
    foreach ($list as $sub) {
        solar_load_test_files($sub);
    }
}

// report all errors
error_reporting(E_ALL|E_STRICT);

// look in this directory for tests
$dir = $_SERVER['argv'][1];

// starting with this class
$base = $_SERVER['argv'][2];

// only this class?
$only = $_SERVER['argv'][3];

// find the top-level file for the base
$class_file = $dir
            . DIRECTORY_SEPARATOR
            . str_replace('_', DIRECTORY_SEPARATOR, $base)
            . ".php";
            
if (file_exists($class_file) && is_readable($class_file)) {
    require_once $class_file;
}

// load all test files under the base dir, if it's not the only one to test
if (! $only) {
    $subdir = substr($class_file, 0, -4);
    solar_load_test_files($subdir);
}

// now that all the files are loaded, let's see what classes we found
$classes = get_declared_classes();
sort($classes);
$data = array('plan' => 0, 'tests' => array());
$count = 0;
foreach ($classes as $class) {
    // is it a Test_* class?
    if (substr($class, 0, 5) == 'Test_') {
        
        // ignore abstracts and interfaces
        $reflect = new ReflectionClass($class);
        if ($reflect->isAbstract() || $reflect->isInterface()) {
            continue;
        }
        
        // find all the test*() methods in the Test_* class
        $methods = get_class_methods($class);
        foreach ($methods as $method) {
            if (substr($method, 0, 4) == 'test') {
                $data['plan'] ++;
                $data['tests'][$class][] = $method;
            }
        }
    }
}

// dump the serialized data
echo serialize($data);

// exit code 104 is "EXIT_PASS"
exit(104);
