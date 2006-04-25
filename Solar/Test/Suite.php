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
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Assert.php 1041 2006-04-04 15:12:36Z pmjones $
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
     * : \\sub\\ : (string) The class name of a sub-test to run instead
     *   of the full suite.  The "Test_" prefix is not needed.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'dir' => '', // where tests are located
        'sub' => '', // only run these sub-tests
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
     * Run this class name sub-test instead of the full suite.
     * 
     * @var string
     * 
     */
    protected $_sub;
    
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
     * Whether or not to silence output.
     * 
     * @var bool
     * 
     */
    protected $_quiet;
    
    /**
     * 
     * A Solar_Debug_Var instance.
     * 
     * @var Solar_Debug_Var
     * 
     */
    protected $_var; // Solar_Debug_Var
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // main construction
        parent::__construct($config);
        
        // where are the tests located?
        $this->_dir = Solar::fixdir($this->_config['dir']);
        
        // set the sub-test class name (drop Test_* prefix)
        $this->_sub = trim($this->_config['sub']);
        if ($this->_sub && substr($this->_sub, 0, 5) == 'Test_') {
            $this->_sub = substr($this->_sub, 5);
        }
        
        // keep a Solar_Debug_Var object around for later
        $this->_var = Solar::factory('Solar_Debug_Var');
    }
    
    /**
     * 
     * Recursively iterates through a directory looking for test classes.
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
            
            if ($iter->isDot() || $file[0] == '.') {
                continue;
            }
    
            if ($iter->isDir() && $iter->hasChildren()) {
            
                $this->findTests($iter->getChildren());
                
            } elseif ($iter->isFile()) {
                
                require_once $path;
                
                $len = strlen($this->_dir);
                $class = substr($path, $len, -4); // drops .php
                $class = 'Test_' . str_replace(DIRECTORY_SEPARATOR, '_', $class);
                $this->addTestMethods($class);
            }
        }
    }
    
    /**
     * 
     * Adds the test methods from a given test class.
     * 
     * @param string $class The Test_* class name to add methods from.
     * 
     * @return void
     * 
     */
    public function addTestMethods($class)
    {
        if (class_exists($class)) {
            /** @todo check if abstract */
            /** @todo check if extends Solar_Test */
            $reflect = new ReflectionClass($class);
            $methods = $reflect->getMethods();
            foreach ($methods as $method) {
                $name = $method->getName();
                if (substr($name, 0, 4) == 'test') {
                    $this->_test[$class][] = $name;
                    $this->_info['plan'] ++;
                }
            }
        }
    }
    
    /**
     * 
     * Runs the test suite (or the sub-test).
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
     * @param bool $quiet True to suppress output, false to display.
     * 
     * @return array A statistics array.
     * 
     */
    public function run($quiet = false)
    {
        $this->_quiet = (bool) $quiet;
        
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
        
        $this->_info['plan'] = 0;
        
        // running all tests, or just a sub-test series?
        if ($this->_sub) {
            $class = $this->_sub;
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
        
        // run the tests
        $time = time();
        $this->_echo("1..{$this->_info['plan']}");
        foreach ($this->_test as $class => $methods) {
            
            // class setup
            $test = Solar::factory($class);
            
            // test each method in the class
            foreach ($methods as $method) {
                
                // info
                $this->_info['done'] ++;
                $name = "$class::$method";
                
                // method setup
                $test->setup();
                
                // test and save
                try {
                    $test->$method();
                    $this->_done('pass', $name);
                } catch (Solar_Test_Exception_Skip $e) {
                    $this->_done('skip', $name, $e->getMessage());
                } catch (Solar_Test_Exception_Todo $e) {
                    $this->_done('todo', $name, $e->getMessage());
                } catch (Solar_Test_Exception_Fail $e) {
                    $this->_done('fail', $name, $e->getMessage(), $e->__toString());
                }
                
                // method teardown
                $test->teardown();
            }
            
            // class teardown
            unset($test);
        }
        
        $this->_info['time'] = time() - $time;
        
        if (! $this->_quiet) {
            $this->_echo($this->_formatInfo());
        }
        
        return $this->_info;
        
    }
    
    /**
     * 
     * Returns the info stats as text.
     * 
     * @return string
     * 
     */
    protected function _formatInfo()
    {
        $done = $this->_info['done'];
        $plan = $this->_info['plan'];
        $time = $this->_info['time'];
        
        $text = array();
        $text[] = "$done/$plan tests, $time seconds";
        $tmp = array();
        foreach ($this->_info as $type => $list) {
            $count = count($list);
            $tmp[] = "$count $type";
        }
        $text[] = implode(', ', $tmp);
        
        $show = array('fail', 'todo', 'skip');
        foreach ($show as $type) {
            foreach ($this->_info[$type] as $name => $note) {
                $text[] = strtoupper($type) . " $name ($note)";
            }
        }
        
        return implode("\n", $text);
    }
    
    /**
     * 
     * Formats a test result, echoes it, and saves the info.
     * 
     * @param string $type Pass, todo, skip, or fail.
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
        $text = '';
        $num = $this->_info['done'];
        
        switch ($type) {
        case 'pass':
            $text = "ok $num - $name";
            break;
        
        case 'skip':
            $text = "ok $num - $name # SKIP"
                  . ($note ? " $note" : "");
            break;
        
        case 'todo':
            $text = "not ok $num - $name # TODO"
                  . ($note ? " $note" : "");
            break;
        
        case 'fail':
            $text = "not ok $num - $name"
                  . ($note ? " $note" : "");
            break;
        }
        
        if (is_array($diag) || is_object($diag)) {
            $diag = $this->_var->dump($diag);
        }
        
        $this->_echo($text);
        $this->_echo($diag, true);
        $this->_info[$type][$name] = $note;
    }
    
    /**
     * 
     * Echoes output, but only when not in quiet mode.
     * 
     * @return void
     * 
     */
    public function _echo($spec, $diag = false)
    {
        if (! trim($spec) || $this->_quiet) {
            return;
        }
        
        if ($diag) {
            echo "# " . str_replace("\n", "\n# ", trim($spec)) . "\n";
        } else {
            echo "$spec\n";
        }
    }
    
}
?>