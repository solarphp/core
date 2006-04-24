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
 *     Example.php  -- Test_Solar_Example
 *     Example/     
 *       Sub.php    -- Test_Solar_Example_Sub
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Suite extends Solar_Base {
    
    protected $_config = array(
        'dir' => '', // where tests are located
        'sub' => '', // only run these sub-tests
    );
    
    protected $_dir; // source directory
    
    protected $_sub; // sub-test class name (with or without Test_ prefix)
    
    protected $_log; // array of log messages
    
    protected $_test; // class => array(method, method, ...)
    
    protected $_plan; // number of test methods
    
    protected $_quiet; // silence "ok" output
    
    protected $_var; // Solar_Debug_Var
    
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
                    $this->_plan ++;
                }
            }
        }
    }
    
    public function run($quiet = false)
    {
        $this->_quiet = $quiet;
        
        $this->_log = array(
            'pass' => array(),
            'skip' => array(),
            'todo' => array(),
            'fail' => array(),
        );
        
        $this->_test = array();
        
        $this->_plan = 0;
        
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
        
        $before = time();
        
        $this->out("1..{$this->_plan}");
        $i = 0;
        
        foreach ($this->_test as $class => $methods) {
            
            /**
             * @todo: check to see if we should run the class test at all;
             * if not, advance $i by the number of test methods in that class,
             * then loop to the next class.
             */
            
            // class setup
            $test = Solar::factory($class);
            
            // test each method in the class
            foreach ($methods as $method) {
                
                
                // info
                $i ++;
                $name = "$class::$method";
                
                // method setup
                $test->setup();
                
                // test and log
                try {
                    $test->$method();
                    $this->log('pass', $i, $name);
                } catch (Solar_Test_Exception_Skip $e) {
                    $this->log('skip', $i, $name, $e->getMessage());
                } catch (Solar_Test_Exception_Todo $e) {
                    $this->log('todo', $i, $name, $e->getMessage());
                } catch (Solar_Test_Exception_Fail $e) {
                    $this->log('fail', $i, $name, $e->getMessage(), $e->__toString());
                }
                
                // method teardown
                $test->teardown();
            }
            
            // class teardown
            unset($test);
        }
        
        $this->displayLog($i, time() - $before);
    }
    
    public function displayLog($numTests, $time)
    {
        // skip if in "quiet" mode and all went well.
        if ($this->_quiet &&
            $numTests == $this->_plan &&
            count($this->_log['fail']) == 0 &&
            count($this->_log['todo']) == 0) {
            // all went well!
            return;
        }
        
        echo "\n$numTests/{$this->_plan} tests, $time seconds\n";
        $tmp = array();
        foreach ($this->_log as $type => $list) {
            $count = count($list);
            $tmp[] = "$count $type";
        }
        echo implode(', ', $tmp) . "\n";
        
        $show = array('fail', 'todo', 'skip');
        foreach ($show as $type) {
            foreach ($this->_log[$type] as $name => $note) {
                echo strtoupper($type) . " $name ($note)\n";
            }
        }
        echo "\n";
    }
    
    public function out($spec, $diag = false)
    {
        if (! trim($spec)) {
            return;
        }
        
        if (substr($spec, 0, 2) == 'ok' && $this->_quiet) {
            return;
        }
        
        if ($diag) {
            echo "# " . str_replace("\n", "\n# ", trim($spec)) . "\n";
        } else {
            echo "$spec\n";
        }
    }
    
    public function log($type, $num, $name, $note = null, $diag = null)
    {
        $text = '';
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
        
        $this->out($text);
        $this->out($diag, true);
        $this->_log[$type][$name] = $note;
    }
}
?>