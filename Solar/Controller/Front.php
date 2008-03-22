<?php
/**
 * 
 * Front-controller class to find and invoke a page-controller.
 * 
 * An example bootstrap "index.php" for your web root using the front
 * controller ...
 * 
 * {{code: php
 *     require 'Solar.php';
 *     Solar::start();
 *     $front = Solar::factory('Solar_Controller_Front');
 *     $front->display();
 *     Solar::stop();
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Controller_Front extends Solar_Base {
    
    /**
     * 
     * User-defined configuration array.
     * 
     * Keys are ...
     * 
     * `classes`
     * : (array) Base class names for page controllers.
     * 
     * `disable`
     * : (array) A list of class names that should be disallowed and treated
     *   as "not found" if a URI maps to them.
     * 
     * `default`
     * : (string) The default controller name (e.g., 'foo-bar').
     * 
     * `routing`
     * : (array) Key-value pairs explicitly mapping a controller name to a
     *   controller class. E.g., 'foo-bar' => 'Vendor_App_FooBar'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Controller_Front = array(
        'classes' => array('Solar_App'),
        'disable' => array('Solar_App_Base'),
        'default' => 'hello',
        'routing' => array(),
    );
    
    /**
     * 
     * The default page-controller name when none is specified.
     * 
     * This is the URI-form or short-form name; i.e., "foo-bar", not "FooBar"
     * or "Vendor_App_FooBar".
     * 
     * @var string
     * 
     */
    protected $_default = 'hello';
    
    /**
     * 
     * A list of class names that should be disallowed and treated as "not
     * found" if a URI maps to them.
     * 
     * 
     * @var array
     * 
     */
    protected $_disable = array('Solar_App_Base');
    
    /**
     * 
     * Explicit page-name to class-name mappings.
     * 
     * @var array
     * 
     */
    protected $_routing = array();
    
    /**
     * 
     * Stack of page-controller class prefixes.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // do the "real" construction
        parent::__construct($config); 
        
        // set convenience vars from config
        $this->_default = (string) $this->_config['default'];
        $this->_disable = (array)  $this->_config['disable'];
        $this->_routing = (array)  $this->_config['routing'];
        
        // set up a class stack for finding apps
        $this->_stack = Solar::factory('Solar_Class_Stack');
        $this->_stack->add($this->_config['classes']);
        
        // extended setup
        $this->_setup();
    }
    
    /**
     * 
     * Sets up the environment for all pages.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Fetches the output of a page/action/info specification URI.
     * 
     * @param Solar_Uri_Action|string $spec An action URI for the front
     * controller.  E.g., 'bookmarks/user/pmjones/php+blog?page=2'. When
     * empty (the default) uses the current URI.
     * 
     * @return string The output of the page action.
     * 
     */
    public function fetch($spec = null)
    {
        if ($spec instanceof Solar_Uri_Action) {
            // a URI was passed directly
            $uri = $spec;
        } else {
            // user spec is a URI string; if empty, is the current URI
            $uri = Solar::factory('Solar_Uri_Action', array(
                'uri' => (string) $spec,
            ));
        }
        
        // take the page name off the top of the path.
        $page = array_shift($uri->path);
        
        // try to get a class name from the page name
        $class = $this->_getPageClass($page);
        if (! $class) {
            
            // put the page name back on top
            array_unshift($uri->path, $page);
            
            // try to get a controller class from the default page name
            $class = $this->_getPageClass($this->_default);
            if (! $class) {
                // no class could be found for the default page name
                return $this->_notFound($page);
            }
        }
        
        // does the page map to a disabled class?
        if (in_array($class, $this->_disable)) {
            return $this->_notFound($page);
        }
        
        // instantiate the controller class and fetch its content
        $obj = Solar::factory($class);
        $obj->setFrontController($this);
        return $obj->fetch($uri);
    }
    
    /**
     * 
     * Displays the output of an page/action/info specification URI.
     * 
     * @param string $spec A page/action/info spec for the front
     * controller. E.g., 'bookmarks/user/pmjones/php+blog?page=2'.
     * 
     * @return string The output of the page action.
     * 
     */
    public function display($spec = null)
    {
        echo $this->fetch($spec);
    }
    
    /**
     * 
     * Finds the page-controller class name from a page name.
     * 
     * @param string $page The page name.
     * 
     * @return string The related page-controller class picked from
     * the routing, or from the list of available classes.  If not found,
     * returns false.
     * 
     */
    protected function _getPageClass($page)
    {
        if (! empty($this->_routing[$page])) {
            // found an explicit route
            $class = $this->_routing[$page];
        } else {
            // no explicit route, try to find a matching class
            $page = str_replace('-',' ', $page);
            $page = str_replace(' ', '', ucwords(trim($page)));
            $class = $this->_stack->load($page, false);
        }
        return $class;
    }
    
    /**
     * 
     * Executes when fetch() cannot find a related page-controller class.
     * 
     * Note that the only time this will execute is when the requested
     * page-controller class **and** the default class cannot be found.
     * 
     * Generates an "HTTP 1.1/404 Not Found" status header and returns a
     * short HTML page describing the error.
     * 
     * @param string $page The URI-form/short-form name of the page not found.
     * 
     * @return string
     * 
     */
    protected function _notFound($page)
    {
        $content = "<html><head><title>Not Found</title>"
                 . "<body><h1>404 Not Found</h1><p>"
                 . htmlspecialchars("Page controller for '$page' not found.")
                 . "</p></body></html>";
        
        $response = Solar::factory('Solar_Http_Response');
        $response->setStatusCode(404);
        $response->content = $content;
        
        return $response;
    }
}