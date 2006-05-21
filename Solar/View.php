<?php
/**
 * 
 * Provides a Template View pattern implementation for Solar.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Provides a Template View pattern implementation for Solar.
 * 
 * This implementation is good for all (X)HTML and XML template
 * formats, and provides a built-in escaping mechanism for values,
 * along with lazy-loading and persistence of helper objects.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'template_path' => array(),
        'helper_class'  => array(),
        'escape'        => array(),
    );
    
    /**
     * 
     * Parameters for escaping.
     * 
     * @var array
     * 
     */
    protected $_escape = array(
        'quotes'  => ENT_COMPAT,
        'charset' => 'iso-8859-1',
    );
    
    /**
     * 
     * Instantiated helper objects.
     * 
     * @var array
     * 
     */
    protected $_helper = array();
    
    /**
     * 
     * Class stack for helpers.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_helper_class;
    
    /**
     * 
     * Path stack for templates.
     * 
     * @var Solar_Path_Stack
     * 
     */
    protected $_template_path;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // base construction
        parent::__construct($config);
        
        // load the base helper class
        Solar::loadClass('Solar_View_Helper');
        
        // set the fallback helper path
        $this->_helper_class = Solar::factory('Solar_Class_Stack'); 
        $this->setHelperClass($this->_config['helper_class']);
        
        // set the fallback template path
        $this->_template_path = Solar::factory('Solar_Path_Stack'); 
        $this->setTemplatePath($this->_config['template_path']);
        
        // special setup
        $this->_setup();
    }
    
    /**
     * 
     * Disallows setting of underscore-prefixed variables.
     * 
     * @param string $key The variable name.
     * 
     * @param string $val The variable value.
     * 
     * @return void
     * 
     */
    public function __set($key, $val)
    {
        if ($key[0] != '_') {
            $this->$key = $val;
        }
    }
    
    /**
     * 
     * Allows specialized setup for extended classes.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        if (! empty($this->_config['escape']['quotes'])) {
            $this->_escape['quotes'] = $this->_config['escape']['quotes'];
        }
        if (! empty($this->_config['escape']['charset'])) {
            $this->_escape['charset'] = $this->_config['escape']['charset'];
        }
    }
    
    /**
     * 
     * Sets variables for the view.
     * 
     * This method is overloaded; you can assign all the properties of
     * an object, an associative array, or a single value by name.
     * 
     * You are not allowed to assign any variable with an underscore
     * prefix.
     * 
     * In the following examples, the template will have two variables
     * assigned to it; the variables will be known inside the template as
     * "$this->var1" and "$this->var2".
     * 
     * <code>
     * $view = Solar::factory('Solar_View_Template');
     * 
     * // assign directly
     * $view->var1 = 'something';
     * $view->var2 = 'else';
     * 
     * // assign by associative array
     * $ary = array('var1' => 'something', 'var2' => 'else');
     * $view->assign($ary);
     * 
     * // assign by object
     * $obj = new stdClass;
     * $obj->var1 = 'something';
     * $obj->var2 = 'else';
     * $view->assign($obj);
     * 
     * // assign by name and value
     * $view->assign('var1', 'something');
     * $view->assign('var2', 'else');
     * </code>
     * 
     * @param mixed $spec The assignment specification.
     * 
     * @param mixed $var (Optional) If $spec is a string, assign
     * this variable to the $spec name.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function assign($spec)
    {
        // assign from associative array
        if (is_array($spec)) {
            foreach ($spec as $key => $val) {
                $this->$key = $val;
            }
            return true;
        }
        
        // assign from object public properties
        if (is_object($spec)) {
            foreach (get_object_vars($spec) as $key => $val) {
                $this->$key = $val;
            }
            return true;
        }
        
        // assign by name and value
        if (is_string($spec) && func_num_args() > 1) {
            $this->$spec = func_get_arg(1);
            return true;
        }
        
        // $spec was not array, object, or string.
        return false;
    }
    
    // -----------------------------------------------------------------
    //
    // Helpers
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Executes a helper method with arbitrary parameters.
     * 
     * @param string $name The helper name.
     * 
     * @param array $args The parameters passed to the helper.
     * 
     * @return string The helper output.
     * 
     */
    public function __call($name, $args)
    {
        $helper = $this->getHelper($name);
        return call_user_func_array(array($helper, $name), $args);
    }
    
    /**
     * 
     * Reset the helper class stack.
     * 
     * @param string|array $list The classes to set for the stack.
     * 
     * @return void
     * 
     * @see Solar_Class_Stack::set()
     * 
     * @see Solar_Class_Stack::add()
     * 
     */
    public function setHelperClass($list = null)
    {
        $this->_helper_class->set('Solar_View_Helper');
        $this->_helper_class->add($list);
    }
    
    /**
     * 
     * Add to the helper directory path stack.
     * 
     * @param string|array $list The classes to add to the stack.
     * 
     * @return void
     * 
     * @see Solar_Class_Stack::add()
     * 
     */
    public function addHelperClass($list)
    {
        $this->_helper_class->add($list);
    }
    
    /**
     * 
     * Returns the helper class stack.
     * 
     * @return array The stack of helper classes.
     * 
     * @see Solar_Class_Stack::get()
     * 
     */
    public function getHelperClass()
    {
        return $this->_helper_class->get();
    }
    
    /**
     * 
     * Returns an internal helper object; creates it as needed.
     * 
     * @param string $name The helper name.  If this helper has not
     * been created yet, this method creates it after loading it from
     * the helper class stack.
     * 
     * @return object An internal helper object.
     * 
     */
    public function getHelper($name)
    {
        if (empty($this->_helper[$name])) {
            $this->_helper[$name] = $this->newHelper($name);
        }
        return $this->_helper[$name];
    }
    
    /**
     * 
     * Creates a new standalone helper object.
     * 
     * @param string $name The helper name.
     * 
     * @param array $config Configuration array for the helper object.
     * 
     * @return object A new standalone helper object.
     * 
     * @see Solar_Class_Stack::load()
     * 
     */
    public function newHelper($name, $config = null)
    {
        $class = $this->_helper_class->load($name);
        settype($config, 'array');
        $config['_view'] = $this;
        $helper = new $class($config);
        return $helper;
    }
    
    /**
     * 
     * Built-in helper for escaping output.
     * 
     * @param scalar $value The value to escape.
     * 
     * @return string The escaped value.
     * 
     */
    public function escape($value)
    {
        return htmlspecialchars(
            $value,
            $this->_escape['quotes'],
            $this->_escape['charset']
        );
    }
    
    // -----------------------------------------------------------------
    //
    // Templates
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Reset the template directory path stack.
     * 
     * @param string|array $path The directories to set for the stack.
     * 
     * @return void
     * 
     * @see Solar_Path_Stack::set()
     * 
     */
    public function setTemplatePath($path = null)
    {
        return $this->_template_path->set($path);
    }
    
    /**
     * 
     * Add to the template directory path stack.
     * 
     * @param string|array $path The directories to add to the stack.
     * 
     * @return void
     * 
     * @see Solar_Path_Stack::add()
     * 
     */
    public function addTemplatePath($path)
    {
        return $this->_template_path->add($path);
    }
    
    /**
     * 
     * Returns the template directory path stack.
     * 
     * @return array The path stack of template directories.
     * 
     * @see Solar_Path_Stack::get()
     * 
     */
    public function getTemplatePath()
    {
        return $this->_template_path->get();
    }
    
    /**
     * 
     * Displays a template directly.
     * 
     * @param string $name The template to display.
     * 
     * @return void
     * 
     */
    public function display($name)
    {
        echo $this->fetch($name);
    }
    
    /**
     * 
     * Fetches template output.
     * 
     * @param string $name The template to process.
     * 
     * @return string The template output.
     * 
     */
    public function fetch($name)
    {
        $file = $this->template($name);
        ob_start();
        $this->_run($file);
        return ob_get_clean();
    }
    
    /**
     * 
     * Returns the path to the requested template script.
     * 
     * Used inside a template script like so:
     * 
     * <code>
     * include $this->template($name);
     * </code>
     * 
     * @param string $name The template name to look for in the template path.
     * 
     * @return string The full path to the template script.
     * 
     */
    public function template($name)
    {
        // get a path to the template
        $file = $this->_template_path->find($name);
        if (! $file) {
            throw $this->_exception(
                'ERR_TEMPLATE_NOT_FOUND',
                array('name' => $name, 'path' => $this->_template_path->get())
            );
        }
        return $file;
    }
    
    /**
     * 
     * Runs a template script (allowing access to $this).
     * 
     * @param string $file The template script to run.
     * 
     * @return void
     * 
     */
    protected function _run()
    {
        require func_get_arg(0);
    }
}
?>