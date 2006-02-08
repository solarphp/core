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
 * @license LGPL
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
 * <code>
 * require_once 'Solar.php';
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
 * @todo How to get data back from the app to use in the layout (e.g., html title?)
 * 
 */
class Solar_Controller_Front extends Solar_Base {

    /**
     * 
     * User-defined configuration array.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'app_class'   => array(
            'hello'     => 'Solar_App_HelloWorld',
            'bookmarks' => 'Solar_App_Bookmarks',
        ),
        'app_default' => 'bookmarks',
        'layout' => '',
        'output_var' => 'solar_app_output',
    );

    /**
     * 
     * The default short-name when none is specified.
     * 
     * @var array
     * 
     */
    protected $_app_default;

    /**
     * 
     * Map of app names to classes.
     * 
     * @var array
     * 
     */
    protected $_app_class;
    
    protected $_layout; // path to the layout template
    
    protected $_output_var; // name of the app-output var in the layout template
    
    /**
     * 
     * Constructor.
     * 
     * @var array
     * 
     */
    public function __construct($config)
    {
        // set the default layout
        $this->_config['layout'] = dirname(__FILE__)
            . '/Front/Layout/default.layout.php';
        
        // now do "real" construction
        parent::__construct($config);
        $this->_app_default = $this->_config['app_default'];
        $this->_app_class   = $this->_config['app_class'];
        $this->_layout      = $this->_config['layout'];
        $this->_output_var  = $this->_config['output_var'];
    }

    /**
     * 
     * Fetches the output of an app/action/info specification URI.
     * 
     * @param string $spec A app/action/info spec for the front
     * controller. E.g., 'bookmarks/user/pmjones/php+blog?page=2'.
     * 
     * @return string The output of the application action.
     * 
     */
    public function fetch($spec = null)
    {
        // default to current URI
        $uri = Solar::factory('Solar_Uri');
        
        // override current URI with user spec
        if (is_string($spec)) {
            $uri->importAction($spec);
        }
        
        // pull the app name off the top of the path_info.
        // note that this alters the URI path_info in-place.
        $name = array_shift($uri->info);
        if (trim($name) == '') {
            $name = $this->_app_default;
        }
        
        /** @todo Add real 404 support. */
        // is it a known app name?
        if (! array_key_exists($name, $this->_app_class)) {
            return htmlspecialchars("404: Page '$name' unknown.");
        }
        
        // instantiate the app class, fetch its output,
        // and fetch its layout values.
        $class = $this->_app_class[$name];
        $app = Solar::factory($class);
        $output = $app->fetch($uri);
        $layout = $app->getLayout();
        
        if (empty($this->_layout) || $layout === false) {
            // one-step view
            return $app->fetch($uri);
        } else {
            // two-step view.
            // set up the layout template.
            $tpl = Solar::factory('Solar_Template');
            
            // step 1:
            // fetch the app output, assign the app vars,
            // then assign the app output (so that the output
            // overrides any related app vars).
            $tpl->assign($layout);
            $tpl->assign($this->_output_var, $output);
            
            // step 2:
            // render the layout.
            $tpl->setPath('template', dirname($this->_layout));
            return $tpl->fetch(basename($this->_layout));
        }
    }

    /**
     * 
     * Displays the output of an app/action/info specification URI.
     * 
     * @param string $spec A app/action/info spec for the front
     * controller. E.g., 'bookmarks/user/pmjones/php+blog?page=2'.
     * 
     * @return string The output of the application.
     * 
     */
    public function display($spec = null)
    {
        echo $this->fetch($spec);
    }
}
?>