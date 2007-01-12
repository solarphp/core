<?php
/**
 * 
 * Front-controller class for Solar.
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
 * Front-controller class for Solar.
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
     * `classes`:
     * (array) Base class names for page controllers.
     * 
     * `default`:
     * (string) The default page name to load.
     * 
     * @var array
     * 
     */
    protected $_Solar_Controller_Front = array(
        'classes' => array('Solar_App'),
        'default' => 'hello',
    );

    /**
     * 
     * List of base class names.
     * 
     * Classes are searched in last-in-first-out order, so later classes take
     * precedence over earlier ones.
     * 
     * @var array
     * 
     */
    protected $_classes;
    
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
        $this->_classes = $this->_config['classes'];
        $this->_default = $this->_config['default'];
        
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
     * controller.  E.g., 'bookmarks/user/pmjones/php+blog?page=2'.
     * 
     * @return string The output of the page action.
     * 
     * @todo Add 404 support.
     * 
     */
    public function fetch($spec = null)
    {
        // default to current URI
        $uri = Solar::factory('Solar_Uri_Action');
        
        // override current URI with user spec
        if (is_string($spec)) {
            $uri->set($spec);
        }
        
        // take the page name off the top of the path.
        // use the default if none specified.
        $page = array_shift($uri->path);
        if (trim($page) == '') {
            $page = $this->_default;
        }
        
        // if there was 0 or 1 element in the original URI path,
        // look for a format extension and strip it. (0 means we
        // used a default page.)
        if (count($uri->path) == 0) {
            $pos = strpos($page, '.');
            if ($pos !== false) {
                // ADD JUST THE FORMAT EXTENSION BACK TO THE PATH-INFO.
                // this allows us to request the default action without
                // needing to specify exactly what that action is.  e.g.,
                // "example.com/controller.xml" becomes "example.com/.xml".
                $dot_format = substr($page, $pos);
                array_unshift($uri->path, $dot_format);
                // strip the format off the page name so we can find the
                // related class.
                $page = substr($page, 0, $pos);
            }
        }
        
        // convert from "pageName, page-name, page_name" to "PageName"
        $page = str_replace(array('_', '-'), ' ', $page);
        $page = str_replace(' ', '', ucwords(trim($page)));
        
        // attempt to map the page name to a page-controller class
        $class = $this->_getPageClass($page);
        if (! $class) {
            // not found, fall back to default
            $class = $this->_getPageClass($this->_default);
        }
        
        // did we find a page-controller class?
        if (! $class) {
            return $this->_notFound($page);
        }
        
        // instantiate the page class and fetch its content
        $page = Solar::factory($class);
        return $page->fetch($uri);
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
     * @return string $class The related page-controller class picked from
     * the list of available classes.  If not found, returns empty.
     * 
     */
    protected function _getPageClass($page)
    {
        // does the page map to a known class?
        $list = (array) $this->_classes;
        foreach (array_reverse($list) as $base) {
            
            // get a class name
            $class = $base . '_' . $page;
            
            // what file would it be?
            $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
            
            // does that file exist?
            if (Solar::fileExists($file)) {
                // $class is set to the proper class name, so break
                // out of the loop
                return $class;
            }
        }
    }
    
    /**
     * 
     * Executes when fetch() cannot find a related page-controller class.
     * 
     * @param string $page The name of the page not found.
     * 
     * @return string
     * 
     */
    protected function _notFound($spec)
    {
        return htmlspecialchars("404: Page '$page' not found.");
    }
}
