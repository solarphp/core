<?php
/**
 * 
 * Class for running suites of unit tests.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Needed for test classes.
 */
Solar::loadClass('Solar_Test');

/**
 * 
 * Class for running suites of unit tests.
 * 
 * Expects a directory structure like this:
 * 
 * Test/
 *   Solar.php      -- Test_Solar
 *   Solar/         
 *     Base.php     -- Test_Solar_Base
 *     Uri.php      -- Test_Solar_Uri
 *     Uri/     
 *       Action.php -- Test_Solar_Uri_Action
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Suite extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * : \\dir\\ : (string) The directory where tests are located.
     * 
     * : \\log\\ : (dependency) A Solar_Log dependency for logging test
     *   results.
     * 
     * : \\error_reporting\\ : (int) The level of error reporting we 
     *   want to catch; default is E_ALL|E_STRICT.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'dir'             => '',
        'log'             => null,
        'error_reporting' => null,
    );
    
    /**
     * 
     * The directory where tests are located.
     * 
     * @var string
     * 
     */
    protected $_dir;
    
    /**
     * 
     * The log of pass/skip/todo/fail messages.
     * 
     * @var array
     * 
     */
    protected $_info;
    
    /**
     * 
     * The test classes (and their methods) to run.
     * 
     * In the form of array($class => array($method1, $method2, ...)).
     * 
     * @var array
     * 
     */
    protected $_test;
    
    /**
     * 
     * A Solar_Log instance.
     * 
     * @var Solar_Log
     * 
     */
    protected $_log;
    
    /**
     * 
     * A Solar_Debug_Var instance.
     * 
     * @var Solar_Debug_Var
     * 
     */
    protected $_var;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // set error_reporting here; doing so in the property 
        // declaration generates errors.
        $this->_config['error_reporting'] = E_ALL | E_STRICT;
        
        // main construction
        parent::__construct($config);
        
        // where are the tests located?
        $this->_dir = Solar::fixdir($this->_config['dir']);
        
        // keep a Solar_Debug_Var object around for later
        $this->_var = Solar::factory('Solar_Debug_Var');
        
        // logging
        if (! empty($this->_config['log'])) {
            // retain the passed log dependency
            $this->_log = Solar::dependency('Solar_Log', $this->_config['log']);
        } else {
            // create a new log object
            $log_config = array(
                'adapter' => 'Solar_Log_Adapter_Echo',
                'format' => '%m',
                'events' => 'test',
                'output' => 'text',
            );
            $this->_log = Solar::factory('Solar_Log', $log_config);
        }
    }
    
    /**
     * 
     * Recursively iterates through a directory looking for test classes.
     * 
     * Skips dot-files and files that do not start with upper-case
     * letters.
     * 
     * @param RecursiveDirectoryIterator $iter Directory iterator.
     * 
     * @return void
     * 
     */
    public function findTests(RecursiveDirectoryIterator $iter = null)
    {
        for ($iter->rewind(); $iter->valid(); $iter->next()) {
        
            $path = $iter->current()->getPathname();
            $file = basename($path);
            
            // skip files not starting with a capital letter
            if ($iter->isDot() ||
                ! ctype_alpha($file[0]) ||
                $file != ucfirst($file)) {
                continue;
            }
    
            if ($iter->isDir() && $iter->hasChildren()) {
                $this->findTests($iter->getChildren());
            } elseif ($iter->isFile()) {
                require_once $path;
                $len = strlen($this->_dir);
                $class = substr($path, $len, -4); // drops .php
                $class = 'Test_' . str_replace(DIRECTORY_SEPARATOR,
                    '_', $class);
                $this->addTestMethods($class);
            }
        }
    }
    
    /**
     * 
     * Adds the test methods from a given test class.
     * 
     * Skips abstract and interface classes.
     * 
     * @param string $class The class name to add methods from,
     * typically a Test_* class.
     * 
     * @return int The number of methods added, or boolean false if the
     * class did not exist.
     * 
     */
    public function addTestMethods($class)
    {
        if (! class_exists($class)) {
            return false;
        }
        
        $reflect = new ReflectionClass($class);
        if ($reflect->isAbstract() || $reflect->isInterface()) {
            return;
        }
        
        $count = 0;
        $methods = $reflect->getMethods();
        foreach ($methods as $method) {
            $name = $method->getName();
            if (substr($name, 0, 4) == 'test') {
                $this->_test[$class][] = $name;
                $this->_info['plan'] ++;
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * 
     * Runs the test suite (or the sub-test series) and logs as it goes.
     * 
     * Returns an array of statistics with these keys:
     * 
     * : \\plan\\ : (int) The planned number of tests.
     * 
     * : \\done\\ : (int) The number of tests actually done.
     * 
     * : \\time\\ : (int) The time, in seconds, it took to run all tests.
     * 
     * : \\pass\\ : (array) Log of tests that passed.
     * 
     * : \\skip\\ : (array) Log of tests that were skipped.
     * 
     * : \\todo\\ : (array) Log of tests that are incomplete.
     * 
     * : \\fail\\ : (array) Log of tests that failed.
     * 
     * @param string $series The sub-test series to run, typically a
     * class name (not including the 'Test_' prefix).
     * 
     * @return array A statistics array.
     * 
     */
    public function run($series = null)
    {
        // prepare
        $this->_prepare($series);
        
        // run the tests
        $time = time();
        $this->_log("1..{$this->_info['plan']}");
        foreach ($this->_test as $class => $methods) {
            
            // class setup
            try {
                $test = Solar::factory($class);
            } catch (Solar_Test_Exception_Skip $e) {
                $this->_info['done'] ++;
                $this->_done('skip', $class, $e->getMessage());
                $this->_info['done'] += count($methods) - 1;
                continue;
            } catch (Solar_Test_Exception_Todo $e) {
                $this->_info['done'] ++;
                $this->_done('todo', $class, $e->getMessage());
                $this->_info['done'] += count($methods) - 1;
                continue;
            } catch (Solar_Test_Exception_Fail $e) {
                $this->_info['done'] ++;
                $this->_done('fail', $class, $e->getMessage(),
                    $e->__toString());
                $this->_info['done'] += count($methods) - 1;
                continue;
            }
            
            // test each method in the class
            foreach ($methods as $method) {
                
                // info
                $this->_info['done'] ++;
                $name = "$class::$method";
                
                // method setup
                $test->setup();
                
                // run test method and check validity
                try {
                    
                    // turn on all error reporting
                    $reporting = ini_get('error_reporting');
                    ini_set('error_reporting', $this->_config['error_reporting']);
                    
                    // turn off error display so that the exceptions
                    // are the only thing generating output
                    $display = ini_get('display_errors');
                    ini_set('display_errors', false);
                    
                    // set the error handler for the test
                    set_error_handler(array($test, 'error'));
            
                    // run the test
                    $test->$method();
                    
                    // check for non-exception failures
                    if (! $test->getAssertCount()) {
                        // no assertions made, which means nothing was
                        // actually tested.
                        $this->_done('todo', $name, 'made no assertions');
                    } else {
                        // no non-exception failures, so it passes.
                        $this->_done('pass', $name);
                    }
                    
                    // return to previous error handler
                    restore_error_handler();
                    
                    // return to previous error display and reporting
                    ini_set('display_errors', $display);
                    ini_set('error_reporting', $reporting);
                    
                } catch (Solar_Test_Exception_Skip $e) {
                    $this->_done('skip', $name, $e->getMessage());
                } catch (Solar_Test_Exception_Todo $e) {
                    $this->_done('todo', $name, $e->getMessage());
                } catch (Solar_Test_Exception_Fail $e) {
                    $this->_done('fail', $name, $e->getMessage(),
                        $e->__toString());
                }
                
                // method teardown
                $test->teardown();
                
                // reset the assertion counter for the next pass
                $test->resetAssertCount();
            }
            
            // class teardown
            unset($test);
        }
        
        $this->_info['time'] = time() - $time;
        
        // report, then return the run information
        $this->_report();
        return $this->_info;
    }
    
    /**
     * 
     * Prepares class properties for a test run.
     * 
     * @param string $series The sub-test series to run, typically a
     * class name (not including the 'Test_' prefix).
     * 
     * @return void
     * 
     */
    protected function _prepare($series = null)
    {
        // reset
        $this->_info = array(
            'plan' => 0,
            'done' => 0,
            'time' => 0,
            'pass' => array(),
            'skip' => array(),
            'todo' => array(),
            'fail' => array(),
        );
        $this->_test = array();
        
        // running all tests, or just a sub-series?
        if ($series) {
            $class = $series;
            $sub = str_replace('_', DIRECTORY_SEPARATOR, $class);
            $dir = $this->_dir . Solar::fixdir($sub);
            $file = rtrim($dir, DIRECTORY_SEPARATOR) . '.php';
            if (is_readable($file)) {
                require_once $file;
                $this->addTestMethods("Test_$class");
            }
        } else {
            $dir = $this->_dir;
        }
        
        // find all remaining tests
        if (is_dir($dir)) {
            $iter = new RecursiveDirectoryIterator($dir);
            $this->findTests($iter);
        }
    }
    
    /**
     * 
     * Generates a report from class properties.
     * 
     * @return void
     * 
     */
    protected function _report()
    {
        // report summary
        $done = $this->_info['done'];
        $plan = $this->_info['plan'];
        $time = $this->_info['time'];
        
        $this->_log("$done/$plan tests, $time seconds");
        
        $tmp = array();
        $show = array('fail', 'todo', 'skip', 'pass');
        foreach ($show as $type) {
            $count = count($this->_info[$type]);
            $tmp[] = "$count $type";
        }
        $this->_log(implode(', ', $tmp));
        
        $show = array('fail', 'todo', 'skip');
        foreach ($show as $type) {
            foreach ($this->_info[$type] as $name => $note) {
                $this->_log(strtoupper($type) . " $name ($note)");
            }
        }
    }
    
    /**
     * 
     * Formats a test line, logs it, and saves the info.
     * 
     * @param string $type Pass, skip, todo, or fail.
     * 
     * @param string $name The test name.
     * 
     * @param string $note Additional note about the test.
     * 
     * @param string $diag Diagnostics for the test.
     * 
     * @return void
     * 
     */
    protected function _done($type, $name, $note = null, $diag = null)
    {
        $message = '';
        $num = $this->_info['done'];
        
        switch ($type) {
        case 'pass':
            $message = "ok $num - $name";
            break;
        
        case 'skip':
            $message = "ok $num - $name # SKIP"
                  . ($note ? " $note" : "");
            break;
        
        case 'todo':
            $message = "not ok $num - $name # TODO"
                  . ($note ? " $note" : "");
            break;
        
        case 'fail':
            $message = "not ok $num - $name"
                  . ($note ? " $note" : "");
            break;
        }
        
        if (is_array($diag) || is_object($diag)) {
            $diag = $this->_var->dump($diag);
        }
        
        $diag = trim($diag);
        if ($diag) {
            $message = "$message\n# " . str_replace("\n", "\n# ", trim($diag));
        }
        
        $this->_log($message);
        $this->_info[$type][$name] = $note;
    }
    
    
    /**
     * 
     * Saves an event and message to the log.
     * 
     * @param string $event The log event type.
     * 
     * @param string $message The log message.
     * 
     * @return boolean True if saved, false if not, null if logging
     * not enabled.
     * 
     * @see Solar_Test_Suite::$_log
     * 
     * @see Solar_Log::save()
     * 
     */
    protected function _log($message)
    {
        $this->_log->save(get_class($this), 'test', $message);
    }
}
?>