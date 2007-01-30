<?php
/**
 *
 * Load the Solar.php file.
 *
 * If Solar.php is already in the include_path, then everything is
 * fine.  However, if it's not in the include_path, we need to check
 * two other locations:
 *
 * # One directory-level above this one; this is good for SVN checkouts
 *   and for tarball downloads when the include_path has not been set
 *   yet.
 *
 * # Three directory-levels above this one; this is good for when Solar
 *   has been PEAR-installed but is, for some reason, not on the
 *   include_path.
 *
 */
@include_once 'Solar.php';

if (! class_exists('Solar')) {
    // assume Solar is at $target/tests/.
    // this is good for SVN checkouts
    // and the as-delivered tarball/pearball.
    @include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Solar.php';
}

if (! class_exists('Solar')) {
    // assume Solar is at $target/tests/Solar/tests/.
    // this is good for when we're already pear-installed.
    @include_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'Solar.php';
}

if (! class_exists('Solar')) {
    // not in any of the known configurations.
    throw new Exception('Cannot find Solar.php.');
}

/**
 *
 * Slightly nicer output for browser. Checking for $_SERVER['SERVER_PROTOCOL']
 * is pretty bulletproof for cli vs browser output, even when using PHP as
 * a cgi (or fast-cgi).
 *
 */
if (isset($_SERVER['SERVER_PROTOCOL'])) {
    echo "<pre>\n";
    ob_implicit_flush();
    set_time_limit(600);
}

/**
 *
 * Now we can proceed with the actual testing.
 *
 */
$config = dirname(__FILE__) . '/config.inc.php';
Solar::start($config);

// force the include_path to be the Solar directory
$old_include_path = ini_get('include_path');
ini_set('include_path', Solar::$dir);

// configure and instantiate the suite
$config = array(
    'dir' => dirname(__FILE__) . '/Test/',
);
$suite = Solar::factory('Solar_Test_Suite', $config);

// run the test series
$series = null;
if (isset($argv[1])) {
    $series = $argv[1];
} elseif (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) {
    $series = $_SERVER['QUERY_STRING'];
}
$suite->run($series);

// put the include_path back
ini_set('include_path', $old_include_path);

// done!
Solar::stop();
?>