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
 * An example front-controller "index.php" for your web root:
 * 
 * <code type="php">
 * require 'Solar.php';
 * Solar::start();
 * $front = Solar::factory('Solar_Controller_Front');
 * $front->display();
 * Solar::stop();
 * </code>
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
     * Keys are:
     * 
     * : \\classes\\ : (array) Base class names for page controllers.
     * 
     * : \\default\\ : (string) The default page name to load.
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
        
        // pull the page name off the top of the path, convert
        // from "pageName" to "PageName".
        $page = array_shift($uri->path);
        if (trim($page) == '') {
            // no page specified, use the default.
            $page = $this->_default;
        }
        $page = ucfirst($page);
        
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
                break;
            }
            
            // not found at all.
            $class = false;
        }
        
        // did we find the page class?
        if (! $class) {
            return htmlspecialchars("404: Page '$page' unknown.");
        }
        
        // instantiate the page class and fetch its content.
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
}
?>