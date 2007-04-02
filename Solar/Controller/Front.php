<?php
/**
 * 
 * Front-controller class to find and invoke a page-controller.
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
     * `default`
     * : (string) The default page-name.
     * 
     * `routing`
     * : (array) Key-value pairs explicitly mapping a page-name to a
     *   controller class.
     * 
     * @var array
     * 
     */
    protected $_Solar_Controller_Front = array(
        'classes' => array('Solar_App'),
        'default' => 'hello',
        'routing' => array(
            'bookmarks'  => 'Solar_App_Bookmarks',
            'hello'      => 'Solar_App_Hello',
            'hello-ajax' => 'Solar_App_HelloAjax',
            'hello-mini' => 'Solar_App_HelloMini',
        ),
    );
    
    /**
     * 
     * The default page name when none is specified.
     * 
     * @var array
     * 
     */
    protected $_default;
    
    /**
     * 
     * Explicit page-name to class-name mappings.
     * 
     * @var array
     * 
     */
    protected $_routing;
    
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
    public function __construct($config)
    {
        // do the "real" construction
        parent::__construct($config); 
        
        // set convenience vars from config
        $this->_default = $this->_config['default'];
        $this->_routing = $this->_config['routing'];
        
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
        
        // take the page name off the top of the path and try to get a
        // controller class from it.
        $page = array_shift($uri->path);
        $class = $this->_getPageClass($page);
        
        // did we get a class from it?
        if (! $class) {
            // put the original segment back on top.
            array_unshift($uri->path, $page);
            // try to get a controller class from the default page name
            $class = $this->_getPageClass($this->_default);
        }
        
        // last chance: do we have a class yet?
        if (! $class) {
            return $this->_notFound($page);
        }
        
        // instantiate the controller class and fetch its content
        $obj = Solar::factory($class);
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
        $page = str_replace('-',' ', $page);
        $page = str_replace(' ', '', ucwords(trim($page)));
        if (! empty($this->_routing[$page])) {
            // found an explicit route
            $class = $this->_routing[$page];
        } else {
            // no explicit route, try to find a matching class
            $class = $this->_stack->load($page, false);
        }
        return $class;
    }
    
    /**
     * 
     * Executes when fetch() cannot find a related page-controller class.
     * 
     * Generates an "HTTP 1.1/404 Not Found" status header and returns a
     * short HTML page describing the error.
     * 
     * @param string $page The name of the page not found.
     * 
     * @return string
     * 
     */
    protected function _notFound($page)
    {
        header("HTTP 1.1/404 Not Found", true, 404);
        return "<html><head><title>Not Found</title><body><h1>404: Not Found</h1><p>"
             . htmlspecialchars("Page controller for '$page' not found.")
             . "</p></body></html>";
    }
}