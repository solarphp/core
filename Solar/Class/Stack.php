<?php
/**
 * 
 * Stack for loading classes from user-defined hierarchies.
 * 
 * @category Solar
 * 
 * @package Solar_Class
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Stack for loading classes from user-defined hierarchies.
 * 
 * As you add classes to the stack, they are searched first when you 
 * call load($name).
 * 
 * @category Solar
 * 
 * @package Solar_Class
 * 
 */
class Solar_Class_Stack extends Solar_Base {
    
    /**
     * 
     * The class stack.
     * 
     * @var array
     * 
     */
    protected $_stack = array();
    
    /**
     * 
     * Gets a copy of the current stack.
     * 
     * @var array
     * 
     */
    public function get()
    {
        return $this->_stack;
    }
    
    /**
     * 
     * Adds one or more classes to the stack.
     * 
     * {{code: php
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add(array('Base1', 'Base2', 'Base3'));
     *     // $stack->get() reveals that the class search order will be
     *     // 'Base1_', 'Base2_', 'Base3_'.
     *     
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1, Base2, Base3');
     *     // $stack->get() reveals that the directory search order will be
     *     // 'Base1_', 'Base2_', 'Base3_', because this is the way the
     *     // filesystem expects a path definition to work.
     *     
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1');
     *     $stack->add('Base2');
     *     $stack->add('Base3');
     *     // $stack->get() reveals that the directory search order will be
     *     // 'Base3_', 'Base2_', 'Base1_', because the later adds
     *     // override the newer ones.
     * }}
     * 
     * @param array|string $list The classes to add to the stack.
     * 
     * @return void
     * 
     */
    public function add($list)
    {
        if (is_string($list)) {
            $list = explode(',', $list);
        }
        
        if (is_array($list)) {
            $list = array_reverse($list);
        }
        
        foreach ((array) $list as $class) {
            $class = trim($class);
            if (! $class) {
                continue;
            }
            // trim all trailing _, then add just one _,
            // and add to the stack.
            $class = rtrim($class, '_') . '_';
            array_unshift($this->_stack, $class);
        }
    }
    
    /**
     * 
     * Clears the stack and adds one or more classes.
     * 
     * {{code: php
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1');
     *     $stack->add('Base2');
     *     $stack->add('Base3');
     *     
     *     // $stack->get() reveals that the directory search order is
     *     // 'Base3_', 'Base2_', 'Base1_'.
     *     
     *     $stack->set('Another_Base');
     *     
     *     // $stack->get() is now array('Another_Base_').
     * }}
     * 
     * @param array|string $list The classes to add to the stack
     * after clearing it.
     * 
     * @return void
     * 
     */
    public function set($list)
    {
        $this->_stack = array();
        return $this->add($list);
    }
    
    /**
     * 
     * Loads a class using the class stack prefixes.
     * 
     * {{code: php
     *     $stack = Solar::factory('Solar_Class_Stack');
     *     $stack->add('Base1');
     *     $stack->add('Base2');
     *     $stack->add('Base3');
     *     
     *     $class = $stack->load('Name');
     *     // $class is now the first instance of '*_Name' found from the         
     *     // class stack, looking first for 'Base3_Name', then            
     *     // 'Base2_Name', then finally 'Base1_Name'.
     * }}
     * 
     * @param string $name The class to load using the class stack.
     * 
     * @return string The full name of the loaded class.
     * 
     */
    public function load($name)
    {
        $name = ucfirst($name);
        foreach ($this->_stack as $prefix) {
            
            // the full class name
            $class = "$prefix$name";
            
            // pre-empt searching
            if (class_exists($class)) {
                return $class;
            }
            
            // the related file
            $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
            
            // does the file exist?
            if (! Solar::fileExists($file)) {
                continue;
            }
            
            // include it in a limited scope. we don't use Solar::run()
            // because we want to avoid exceptions.
            $this->_run($file);
            
            // did the class exist within the file?
            if (class_exists($class)) {
                // yes, we're done
                return $class;
            }
        }
        
        // failed to find the class in the stack
        throw $this->_exception(
            'ERR_CLASS_NOT_FOUND',
            array(
                'name'  => $name,
                'stack' => $this->_stack,
            )
        );
    }
    
    /**
     * 
     * Loads the class file in a limited scope.
     * 
     * @param string The file to include.
     * 
     */
    protected function _run()
    {
        include func_get_arg(0);
    }
}
?>