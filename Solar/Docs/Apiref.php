<?php
/**
 * 
 * Recursively parses a class directory for API reference documentation.
 * 
 * @category Solar
 * 
 * @package Solar_Docs
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
 * Recursively parses a class directory for API reference documentation.
 * 
 * @category Solar
 * 
 * @package Solar_Docs
 * 
 * @todo parse constants
 * 
 * @todo report when a method is missing documentation (at least a summary)
 * 
 * @todo report when a property is missing documentation (at least a summary)
 * 
 * @todo actually set up a log object
 * 
 */
class Solar_Docs_Apiref extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `phpdoc`
     * : (dependency) A Solar_Docs_Phpdoc dependency.
     * 
     * `log`
     * : (dependency) A Solar_Log dependency.
     * 
     * `unknown`
     * : (string) When a type is unknown or not specified,
     *   use this value instead.
     * 
     * @var array
     * 
     */
    protected $_Solar_Docs_Apiref = array(
        'phpdoc'  => null,
        'log'     => null,
        'unknown' => 'void',
    );
    
    /** 
     * 
     * Solar_Log instance.
     * 
     * @var Solar_Log
     * 
     */
    protected $_log;
    
    /**
     * 
     * Class for parsing PHPDoc comment blocks.
     * 
     * @var Solar_Docs_Phpdoc
     * 
     */
    protected $_phpdoc;
    
    /** 
     * 
     * When generating log notices, ignore these class methods and
     * properties.
     * 
     * @var string
     * 
     * @todo replace with a check for "built-in" classes?
     * 
     */
    protected $_ignore = array(
        'Exception' => array(
            'methods' => array(
                '__clone',
                'getMessage',
                'getCode',
                'getFile',
                'getLine',
                'getTrace',
                'getTraceAsString',
            ),
            'properties' => array(
                'message',
                'code',
                'file',
                'line',
            ),
        ),
    );
    
    /** 
     * 
     * The entire API as a structured array.
     * 
     * <code>
     * $api = array(
     *     classname => array(
     *         summ => string,
     *         narr => string,
     *         tech => array(...),
     *         from => array(...),
     *         properties => array(
     *             propertyname => array(
     *                 name => string,
     *                 summ => string,
     *                 narr => string,
     *                 tech => array(...),
     *                 type => string,
     *                 access => string,
     *                 static => bool,
     *                 from => string,
     *             ), // propertyname
     *         ), // properties
     *         methods => array(
     *             methodname => array(
     *                 name => string,
     *                 summ => string,
     *                 narr => string,
     *                 tech => array(...),
     *                 access => string,
     *                 static => bool,
     *                 final => bool,
     *                 return => string,
     *                 from => string,
     *                 params => array(
     *                     paramname => array(
     *                         name => string,
     *                         type => string,
     *                         summ => string,
     *                         byref => bool,
     *                         optional => bool,
     *                         default => mixed,
     *                     ), // paramname
     *                 ), // params
     *             ), // methodname
     *         ), // methods
     *     ), // classname
     * ); // $this->api
     * }}
     * 
     * @var array
     * 
     */
    public $api = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // PHPDoc parser
        $this->_phpdoc = Solar::dependency(
            'Solar_Docs_Phpdoc',
            $this->_config['phpdoc']
        );
        
        // Logger
        $this->_log = Solar::dependency(
            'Solar_Log',
            $this->_config['log']
        );
    }
    
    /**
     * 
     * Adds classes from a file hierarchy.
     * 
     * @param string $base The base of the class hierarchy, typically
     * the base PEAR library path.
     * 
     * @param string $class Start with this class in the hierarchy.
     * 
     * @return void
     * 
     */
    public function addFiles($base, $class = null)
    {
        $map = Solar::factory('Solar_Class_Map');
        $source = $map->fetch($base, $class);
        foreach ($source as $class => $file) {
            require_once($file);
            $this->addClass($class);
        }
    }
    
    /**
     * 
     * Adds a class to the API docs.
     * 
     * @param string $class The class to add to the docs.
     * 
     * @param bool True if the class was added, false if not.
     * 
     */
    public function addClass($class)
    {
        if (! class_exists($class)) {
            return false;
        }
        
        // add top-level class docs
        $reflect = new ReflectionClass($class);
        $this->api[$class] = $this->_phpdoc->parse($reflect->getDocComment());
        
        // add the class parents, properties and methods
        $this->_addParents($class);
        $this->_addProperties($class);
        $this->_addMethods($class);
        
        // done!
        return true;
    }
        
    
    /**
     * 
     * Adds the inheritance hierarchy for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _addParents($class)
    {
        $parent = $class;
        $parents = array();
        while ($parent = get_parent_class($parent)) {
            $parents[] = $parent;
        }
        $this->api[$class]['from'] = array_reverse($parents);
    }
    
    /**
     * 
     * Adds the property reflections for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _addProperties($class)
    {
        $this->api[$class]['properties'] = array();
        $reflect = new ReflectionClass($class);
        
        foreach ($reflect->getProperties() as $prop) {
        
            // the property name
            $name = $prop->getName();
            
            // comment docs
            $docs = $this->_phpdoc->parse($prop->getDocComment());
            
            // basic properties
            $info = array(
                'name'   => $name,
                'summ'   => $docs['summ'],
                'narr'   => $docs['narr'],
                'tech'   => $docs['tech'],
                'type'   => null,
                'access' => null,
                'static' => $prop->isStatic() ? 'static' : false,
                'from' => false,
            );
            
            // set the access type
            if ($prop->isPublic()) {
                $info['access'] = "public";
            } elseif ($prop->isProtected()) {
                $info['access'] = "protected";
            } elseif ($prop->isPrivate()) {
                $info['access'] = "private";
            }
            
            // add the type
            if (! empty($docs['tech']['var']['type'])) {
                $info['type'] = $docs['tech']['var']['type'];
            } else {
                if (! empty($this->_ignore[$class]['properties']) &&
                    ! in_array($name, $this->_ignore[$class]['properties'])) {
                    // not to be ignored
                    $this->_log($class, "property '$name' has no @var type");
                }
            }
            
            // save in the API
            $this->api[$class]['properties'][$name] = $info;
            
            // was it inherited after all?
            $inherited = $this->_isInheritedProperty($class, $prop);
            $this->api[$class]['properties'][$name]['from'] = $inherited;
            
        }
        
        // sort them
        ksort($this->api[$class]['properties']);
    }
    
    /**
     * 
     * Adds the method reflections for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _addMethods($class)
    {
        $this->api[$class]['methods'] = array();
        
        $reflect = new ReflectionClass($class);
        
        foreach ($reflect->getMethods() as $method) {
            
            // get the method name
            $name = $method->getName();
            
            // parse the doc comments
            $docs = $this->_phpdoc->parse($method->getDocComment());
            
            // the basic method information
            $info = array(
                'from' => false,
                'name'   => $name,
                'summ'   => $docs['summ'],
                'narr'   => $docs['narr'],
                'tech'   => $docs['tech'],
                'access' => null,
                'static' => $method->isStatic() ? 'static' : false,
                'final'  => $method->isFinal() ? 'final' : false,
                'return' => null,
                'byref'  => $method->returnsReference() ? '&' : false,
                'params' => array(),
            );
            
            // add the access visibility
            if ($method->isPublic()) {
                $info['access'] = 'public';
            } elseif ($method->isProtected()) {
                $info['access'] = 'protected';
            } elseif ($method->isPrivate()) {
                $info['access'] = 'private';
            }
            
            // find the return type in the technical docs
            if ($method->isConstructor()) {
                // it's a constructor, so it returns its own class
                $info['return'] = $class;
            } elseif (! empty($docs['tech']['return']['type'])) {
                // return type comes from tech docs
                $info['return'] = $docs['tech']['return']['type'];
            } else {
                // no return type noted in the class docs
                $info['return'] = $this->_config['unknown'];
                
                // can we ignore this lack of type?
                if (! empty($this->_ignore[$class]['methods']) &&
                    ! in_array($name, $this->_ignore[$class]['methods'])) {
                    // not to be ignored
                    $unknown = $this->_config['unknown'];
                    $this->_log($class, "method '$name' has unknown @return type, used '$unknown'");
                }
            }
            
            // add the parameters
            $info['params'] = $this->_getParameters($class, $method, $docs['tech']);
            
            // save in the API
            $this->api[$class]['methods'][$name] = $info;
            
            // was it inherited after all?
            $inherited = $this->_isInheritedMethod($class, $method);
            $this->api[$class]['methods'][$name]['from'] = $inherited;
        }
        
        // sort them
        ksort($this->api[$class]['methods']);
    }
    
    /**
     * 
     * Reports the class, if any, a method is inherited from and identical to.
     * 
     * @param string $class The class to check.
     * 
     * @param ReflectionMethod $method The method to check.
     * 
     * @return string The class from which the method was inherited, but only
     * if the modifiers, parameters, and comments are identical.
     * 
     */
    protected function _isInheritedMethod($class, ReflectionMethod $method)
    {
        $name = $method->getName();
        $mods = $method->getModifiers();
        $args = $method->getParameters();
        $docs = $method->getDocComment();
        foreach ($this->api[$class]['from'] as $parent) {
            $parentReflect = new ReflectionClass($parent);
            if ($parentReflect->hasMethod($name)) {
                $parentMethod = $parentReflect->getMethod($name);
                $parent_mods = $parentMethod->getModifiers();
                $parent_args = $parentMethod->getParameters();
                $parent_docs = $parentMethod->getDocComment();
                if ($mods == $parent_mods && $args == $parent_args && $docs == $parent_docs) {
                    return $parent;
                }
            }
        }
        return false;
    }
    
    /**
     * 
     * Reports the class, if any, a property is inherited from and identical to.
     * 
     * @param string $class The class to check.
     * 
     * @param ReflectionProperty $property The property to check.
     * 
     * @return string The class from which the property was inherited, but only
     * if the modifiers and comments are identical.
     * 
     */
    protected function _isInheritedProperty($class, ReflectionProperty $property)
    {
        $name = $property->getName();
        $mods = $property->getModifiers();
        $docs = $property->getDocComment();
        foreach ($this->api[$class]['from'] as $parent) {
            $parentReflect = new ReflectionClass($parent);
            if ($parentReflect->hasProperty($name)) {
                $parentProperty = $parentReflect->getProperty($name);
                $parent_mods = $parentProperty->getModifiers();
                $parent_docs = $parentProperty->getDocComment();
                if ($mods == $parent_mods && $docs == $parent_docs) {
                    return $parent;
                }
            }
        }
        return false;
    }
    
    /**
     * 
     * Returns the parameters for a ReflectionMethod.
     * 
     * @param string $class The class name.
     * 
     * @param ReflectionMethod $method A ReflectionMethod object to get parameters for.
     * 
     * @param array $tech A technical information array derived from Solar_Docs_Phpdoc.
     * 
     * @return array An array of parameter specifications.
     * 
     */
    protected function _getParameters($class, ReflectionMethod $method, $tech)
    {
        $params = array();
        $methodname = $method->getName();
        
        // find each of the parameters
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            $params[$name] = array(
                'name'     => $name,
                'type'     => 'unknown',
                'summ'     => null,
                'byref'    => $param->isPassedByReference() ? '&' : false,
                'optional' => $param->isOptional(),
                'default'  => $param->isOptional() ? $param->getDefaultValue() : null,
            );
            
            // add the type
            if ($param->getClass()) {
                
                // the type comes from a typehint.
                $params[$name]['type'] = $param->getClass();
                
                // hack, because of return differences between PHP5.1.4
                // and earlier PHP5.1.x versions.  otherwise you get
                // things like "Object id #31" as the type.
                if (is_object($params[$name]['type'])) {
                    $params[$name]['type'] = $params[$name]['type']->name;
                }
                
            } elseif (! empty($tech['param'][$name]['type'])) {
                // the type comes from the tech docs
                $params[$name]['type'] = $tech['param'][$name]['type'];
            } else {
                // no typehint, and not in the class docs
                $this->_log($class, "method '$methodname' param '$name' has no type");
            }
            
            // add the summary
            if (! empty($tech['param'][$name]['summ'])) {
                // summary comes from the tech docs
                $params[$name]['summ'] = $tech['param'][$name]['summ'];
            } else {
                // no summary
                $this->_log($class, "method '$methodname' param '$name' has no summary");
            }
        }
        return $params;
    }
    
    /**
     * 
     * Saves a message to the log.
     * 
     * @param string $class The class that the message refers to.
     * 
     * @param string $message The event message.
     * 
     * @return void
     * 
     */
    protected function _log($class, $message)
    {
        $message = "$class: $message";
        $this->_log->save(get_class($this), 'docs', $message);
    }
}
?>