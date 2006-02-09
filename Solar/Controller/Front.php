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
 * <code type="php">
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
            'bookmark'   => 'Solar_App_Bookmarks',
            'bookmarks'  => 'Solar_App_Bookmarks',
            'hello'      => 'Solar_App_HelloWorld',
            'helloworld' => 'Solar_App_HelloWorld',
        ),
        'app_default' => 'bookmarks',
        'layout_dir'  => '',
        'layout_tpl'  => 'default',
        'layout_var'  => 'solar_app_content',
        'construct'   => '',
        'destruct'    => '',
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
    
    /**
     * 
     * Where the layout directory is located.
     * 
     * Defaults is 'Solar/Layout/'.
     * 
     * @var string
     * 
     */
    protected $_layout_dir;
    
    /**
     * 
     * The name of the layout template, minus the .layout.php suffix.
     * 
     * Default is 'default' (i.e., 'default.layout.php').
     * 
     * @var string
     * 
     */
    protected $_layout_tpl;
    
    /**
     * 
     * The name of the app content var in the layout template.
     * 
     * Default is 'solar_app_content'.
     * 
     * @var string
     * 
     */
    protected $_layout_var;
    
    /**
     * 
     * A script to run at __construct() time.
     * 
     * Generally useful for setting up the application environment,
     * processing logins, etc.
     * 
     * Default is 'Solar/Front/construct.php'.
     * 
     * @var string
     * 
     */
    protected $_construct;
    
    /**
     * 
     * A script to run at __destruct() time.
     * 
     * Generally useful for tearing down the application environment.
     * 
     * Default is null.
     * 
     * @var string
     * 
     */
    protected $_destruct;
    
    /**
     * 
     * Constructor.
     * 
     * Runs user-specified construct-time script.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config)
    {
        // set some defaults
        $dir = dirname(__FILE__);
        
        // layout dir is Solar/Layout/
        $this->_config['layout_dir'] = dirname($dir) . "/Layout";
        $this->_config['layout_tpl'] = 'default';
        
        // construction hook
        $this->_config['construct']  = "$dir/Front/construct.hook.php";
        
        // now do "real" construction
        parent::__construct($config); 
        
        // set convenience vars from config
        $this->_app_default = $this->_config['app_default'];
        $this->_app_class   = $this->_config['app_class'];
        $this->_layout_dir  = $this->_config['layout_dir'];
        $this->_layout_tpl  = $this->_config['layout_tpl'];
        $this->_layout_var  = $this->_config['layout_var'];
        $this->_construct   = $this->_config['construct'];
        $this->_destruct    = $this->_config['destruct'];
        
        // execute construct-time hook
        if ($this->_construct) {
            Solar::run($this->_construct);
        }
    }
    
    /**
     * 
     * Runs user-specified destruct-time script.
     * 
     */
    public function __destruct()
    {
        if ($this->_destruct) {
            Solar::run($this->_destruct);
        }
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
        $name = array_shift($uri->info);
        if (trim($name) == '') {
            // no app specified, use the default.
            $name = $this->_app_default;
        }
        
        /** @todo Add real 404 support. */
        // is it a known app name?
        if (! array_key_exists($name, $this->_app_class)) {
            return htmlspecialchars("404: Page '$name' unknown.");
        }
        
        // instantiate the app class and fetch its content.
        $class   = $this->_app_class[$name];
        $app     = Solar::factory($class);
        $content = $app->fetch($uri);
        
        // did the app set any data for the layout?
        $layout = $app->getLayout();
        if ($layout === false) {
            // the app explicitly does not want to use
            // the layout, so fall back to a one-step view
            // and just return the app content.  typically
            // this is the case in things like RSS feeds.
            return $content;
        } else {
            // set up the layout template for a two-step view.
            $tpl = Solar::factory('Solar_Template');
            
            // step 1:
            // assign the app's layout data, then assign the app content
            // (so that the content overrides any related app data).
            $tpl->assign($layout);
            $tpl->assign($this->_layout_var, $content);
            
            // step 2:
            // fetch the layout with the content and vars.
            $tpl->setPath('template', $this->_layout_dir);
            return $tpl->fetch($this->_layout_tpl . '.layout.php');
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