<?php
/**
 * 
 * Command to run a Solar test series.
 * 
 * Synopsis
 * ========
 * 
 * `**solar run-tests** [options] [CLASS]`
 * 
 * If `CLASS` is empty, runs all test classes in the test directory, and 
 * recursively descends into subdirectories to run those tests as well.
 * 
 * If `CLASS` is given, runs that test class, and recursively descends into
 * its subdirectory to run tests there as well.
 * 
 * If the --only option is specified, does not run tests in subdirectories.
 * 
 * 
 * Options
 * =======
 * 
 * `--config FILE`
 * : Path to the Solar.config.php file.  Default false.
 * 
 * `--dir _arg_`
 * : Directory where the test classes are located.  Default is the current
 *   working directory.
 * 
 * `--only`
 * : Run only the named test class, do not recurse into subdirectories.
 * 
 * 
 * Examples
 * ========
 * 
 * Run the whole suite of Solar tests:
 * 
 *     $ cd /path/to/tests/
 *     $ solar run-tests
 * 
 * Run "remotely":
 * 
 *     $ solar run-tests --dir /path/to/tests
 * 
 * Run the Vendor_Example test and all its subdirectories:
 * 
 *     $ solar run-tests Vendor_Example
 * 
 * Run only the Vendor_Example test (no subdirectories):
 * 
 *     $ solar run-tests --only Vendor_Example
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Cli_RunTests extends Solar_Cli_Base
{
    /**
     * 
     * Runs the tests for a class, descending into subdirectories unless
     * otherwise specified.
     * 
     * @param string $class The class to run tests for.
     * 
     * @return void
     * 
     */
    protected function _exec($class = null)
    {
        if (! $class) {
            throw $this->_exception('ERR_NEED_CLASS_NAME');
        }
        
        // look for a test directory, otherwise assume that the tests are
        // in the same dir.
        $dir = $this->_options['dir'];
        if (! $dir) {
            $dir = getcwd();
        }
        
        // make sure it matches the OS
        $dir = Solar_Dir::fix($dir);
        
        // feedback
        $this->_outln("Run tests from '$dir'.");
        
        // make sure it ends in "/Test/".
        $end = DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR;
        if (substr($dir, -5) != $end) {
            $dir = rtrim($dir, DIRECTORY_SEPARATOR) . $end;
        }
        
        // run just the one test?
        $only = (bool) $this->_options['only'];
        
        // set up a test suite object 
        $runner = Solar::factory('Solar_Test_Runner', array(
            'dir'       => $dir,
            'verbose'   => $this->_options['verbose'],
        ));
        
        // run the suite
        $runner->run($class, $only);
    }
}
