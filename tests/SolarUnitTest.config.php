<?php

/**
 *
 * This file sets up environment for testing Solar with PHPUnit3.
 *
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


/**
 *
 * Path to PHPUnit3
 *
 * Leave empty if PHPUnit3 is already in your include_path.
 *
 */
$config['phpUnit_path'] = get_include_path();


/**
 *
 * Path to Solar
 *
 * This file assumes you are testing a repository checkout, so it assumes that
 * Solar is located two directories above this.  Change the copy of Solar you
 * wish to test is located in another directory.
 *
 * Leave empty if you want to test a copy of Solar that is in your 
 * include_path.
 *
 */
$config['solar_path'] = dirname(__FILE__) . '/../../';


/**
 *
 * Path so support directory
 *
 * This is set as the first directory in the include_path and as the constant
 * _TEST_SUPPORT_PATH that can be used to manually set path locations inside 
 * tests as needed.
 *
 */
$config['support_path'] = dirname(__FILE__) . '/support/';


/**
 *
 * A list of paths to include in the include_path
 *
 */
$config['code_path'] = array();

/**
 *
 * An associative array of constants to define
 *
 */
$config['constants'] = array(
    '_TEST_SUPPORT_PATH' => $config['support_path'],
);


/**
 *
 * Location of external configs
 *
 */
$config['config_path'] = dirname(__FILE__) . '/configs/';

/**
 *
 * Nothing below this point should need to be changed.
 *
 * Setup the include_path based on the paths that were provided above.
 *
 */

define('SOLAR_GROUP', true);
// include all of the configs located in configs/
foreach (scandir($config['config_path']) as $file) {
    if (!preg_match('/config.php/i', $file)) {
        continue;
    }
    
    include $config['config_path'] . $file;
}

require dirname(__FILE__) . '/configRunner.php';
?>
