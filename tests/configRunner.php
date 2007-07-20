<?php
/**
 *
 * The runner for config setup for the PHPUnit3 tests
 *
 * @author Travis Swicegood <development [at] domain51 [dot] com>
 *
 * @package Solar
 *
 * @subpackage UnitTests
 *
 * @license Release under the same license as Solar
 *
 * @ignore
 */
// set all of the known code_paths
if (count($config['code_path']) > 0) {
    set_include_path(implode(PATH_SEPARATOR, $config['code_path']) . PATH_SEPARATOR . get_include_path());
}

// set Solar path
if (!empty($config['solar_path'])) {
    set_include_path($config['solar_path'] . PATH_SEPARATOR . get_include_path());
}

// add path for test support directory and give priority.
set_include_path($config['support_path'] . PATH_SEPARATOR . get_include_path());

// set PHPUnit3 path, making sure it's first on the list
if (isset($config['phpUnit_path'])) {
    set_include_path($config['phpUnit_path'] . PATH_SEPARATOR . get_include_path());
}

// set support path constant
foreach ($config['constants'] as $const_name => $const_value) {
    define($const_name, $const_value);
}

// Include Solar.php so the base Solar object is available.
include_once 'Solar.php';

unset($config);