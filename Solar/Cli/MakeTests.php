<?php
/**
 * 
 * Command to make a test class (or set of classes) from a given class.
 * 
 * The class should be in the include_path.
 * 
 * Synopsis
 * ========
 * 
 * `**solar make-tests** [options] CLASS`
 * 
 * Options
 * =======
 * 
 * `--config FILE`
 * : Path to the Solar.config.php file.  Default false.
 * 
 * `--target _arg_`
 * : Directory where the test classes should be written to.  Default is the
 *   current working directory.
 * 
 * `--only`
 * : Make only the test for the given class, do not recurse into subdirectories.
 * 
 * Examples
 * ========
 * 
 * Make test files for a class and its subdirectories.
 * 
 *     $ cd /path/to/tests/
 *     $ solar make-tests Vendor_Example
 * 
 * Make "remotely":
 * 
 *     $ solar make-tests --dir /path/to/tests Vendor_Example
 * 
 * Make only the Vendor_Example test (no subdirectories):
 * 
 *     $ solar make-tests --only Vendor_Example
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
class Solar_Cli_MakeTests extends Solar_Cli_Base
{
    /**
     * 
     * Skeleton templates for classes and methods.
     * 
     * @var array
     * 
     */
    protected $_tpl;
    
    /**
     * 
     * The source class to work with.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * The target directory for writing tests.
     * 
     * @var string
     * 
     */
    protected $_target;
    
    /**
     * 
     * Name of the test file to work with.
     * 
     * @var string
     * 
     */
    protected $_file;
    
    /**
     * 
     * The code in the test file.
     * 
     * @var string
     * 
     */
    protected $_code;
    
    /**
     * 
     * Builds one or more test files starting at the requested class, usually
     * descending recursively into subdirectories of that class.
     * 
     * @param string $class The class name to build tests for.
     * 
     * @return void
     * 
     */
    protected function _exec($class = null)
    {
        $this->_outln("Making tests.");
        
        // make sure we have a class to work with
        if (! $class) {
            throw $this->_exception('ERR_NO_CLASS_SPECIFIED');
        }
        
        // make sure we have a target directory
        $this->_setTarget();
        
        // get all the class and method templates
        $this->_loadTemplates();
        
        // build a class-to-file map object for later use
        $map = Solar::factory('Solar_Class_Map');
        
        // tell the user where the source and targets are
        $this->_outln("Source: " . $map->getBase());
        $this->_outln("Target: $this->_target");
        
        // get the class and file locations
        $class_file = $map->fetch($class);
        foreach ($class_file as $class => $file) {
            
            // tell the user what class we're on
            $this->_out("$class: "); 
            
            // if this is an exception class, skip it
            if (strpos($class, '_Exception')) {
                $this->_outln("skip (exception class)");
                continue;
            }
            
            // load the class and get its API reference
            Solar_Class::autoload($class);
            $apiref = Solar::factory('Solar_Docs_Apiref');
            $apiref->addClass($class);
            $api = $apiref->api[$class];
            
            // set the file name, creating if needed
            $this->_setFile($class, $api);
            
            // get the code currently in the file
            $this->_code = file_get_contents($this->_file);
            
            // add new test methods
            $this->_addTestMethods($api);
            
            // write the file back out again
            file_put_contents($this->_file, $this->_code);
            
            // done with this class
            $this->_outln(' ;');
        }
        
        // done with all classes.
        $this->_outln('Done.');
    }
    
    /**
     * 
     * Loads the template array from skeleton files.
     * 
     * @return void
     * 
     */
    protected function _loadTemplates()
    {
        $this->_tpl = array();
        $dir = Solar_Dir::fix(dirname(__FILE__) . '/MakeTests/Data');
        $list = glob($dir . '*.php');
        foreach ($list as $file) {
            $key = substr(basename($file), 0, -4);
            $text = file_get_contents($file);
            if (substr($key, 0, 5) == 'class') {
                // we need to add the php-open tag ourselves, instead of
                // having it in the template file, becuase the PEAR packager
                // complains about parsing the skeleton code.
                $text = "<?php\n$text";
            }
            $this->_tpl[$key] = $text;
        }
    }
    
    /**
     * 
     * Sets the base directory target.
     * 
     * @return void
     * 
     */
    protected function _setTarget()
    {
        // look for a test directory, otherwise assume that the tests are
        // in the same dir
        $this->_target = $this->_options['target'];
        if (! $this->_target) {
            $this->_target = getcwd();
        }
        
        // make sure it matches the OS.
        $this->_target = Solar_Dir::fix($this->_target);
        
        // make sure it exists
        if (! is_dir($this->_target)) {
            throw $this->_exception('ERR_TARGET_NOT_EXIST');
        }
    }
    
    /**
     * 
     * Sets the file name for the test file, creating it if needed.
     * 
     * Uses a different class template for abstract, factory, and normal
     * (concrete) classes.  Also looks to see if this is an Adapter-based
     * class and takes that into account.
     * 
     * @param string $class The class name to work with.
     * 
     * @param array $api The list of methods in the class API to write test
     * stubs for.
     * 
     * @return void
     * 
     */
    protected function _setFile($class, $api)
    {
        $this->_file = $this->_target 
                     . str_replace('_', DIRECTORY_SEPARATOR, "Test_$class")
                     . '.php';
        
        // create the file if needed
        if (file_exists($this->_file)) {
            return;
        }
        
        // create the directory if needed
        $dir = dirname($this->_file);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        // use the right code template
        if ($api['abstract']) {
            $code = $this->_tpl['classAbstract'];
        } elseif (! empty($api['methods']['solarFactory'])) {
            $code = $this->_tpl['classFactory'];
        } else {
            $code = $this->_tpl['classConcrete'];
        }
        
        // use the right "extends" for adapter classes.
        // extends this to helpers and abstracts?
        // note that this still writes the new methods, when they should
        // be inherited from the parent test instead.
        $pos = strrpos($class, '_Adapter_');
        if ($pos) {
            $code = $this->_tpl['classAdapter'];
            $extends = 'Test_' . substr($class, 0, $pos + 8);
        } else {
            $extends = 'Solar_Test';
        }
        
        // do replacements
        $code = str_replace(
            array('{:class}', '{:extends}'),
            array($class, $extends),
            $code
        );
        
        // write the file
        file_put_contents($this->_file, $code);
    }
    
    
    /**
     * 
     * Adds test methods to the code for a test file.
     * 
     * @param array $api The list of methods in the class API to write test
     * stubs for.
     * 
     * @return void
     * 
     */
    protected function _addTestMethods($api)
    {
        // prepare the testing code for appending new methods.
        $this->_code = trim($this->_code);
        
        // the last char should be a brace.
        $last = substr($this->_code, -1);
        if ($last != '}') {
            throw $this->_exception('ERR_LAST_BRACE', array(
                'file' => $this->_file
            ));
        }
        
        // strip the last brace
        $this->_code = substr($this->_code, 0, -1);
        
        // ignore these methods
        $ignore = array('__construct', '__destruct', 'apiVersion', 'dump',
            'locale');
        
        // look for methods and add them if needed
        foreach ($api['methods'] as $name => $info) {
            
            // is this an ignored method?
            if (in_array($name, $ignore)) {
                $this->_out('.');
                continue;
            }
            
            // is this a public method?
            if ($info['access'] != 'public') {
                $this->_out('.');
                continue;
            };
            
            // the test-method name
            $test_name = 'test' . ucfirst($name);
            
            // does the test-method definition already exist?
            $def = "public function {$test_name}()";
            $pos = strpos($this->_code, $def);
            if ($pos) {
                $this->_out('.');
                continue;
            }
            
            // use the right code template
            if ($info['abstract']) {
                $test_code = $this->_tpl['methodAbstract'];
            } else {
                $test_code = $this->_tpl['methodConcrete'];
            }
            
            // do replacements
            $test_code = str_replace(
                array('{:name}', '{:summ}'),
                array($test_name, $info['summ']),
                $test_code
            );
            
            // append to the test code
            $this->_code .= $test_code;
            $this->_out('+');
        }
        
        // append the last brace
        $this->_code = trim($this->_code) . "\n}\n";
    }
}
