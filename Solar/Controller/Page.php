<?php
/**
 * 
 * Abstract page-based application controller class for Solar.
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
 * Load Solar_Uri_Action for dispatch comparisons.
 */
Solar::loadClass('Solar_Uri_Action');

/**
 * 
 * Abstract page-based application controller class for Solar.
 * 
 * Expects a directory structure like this example:
 * 
 * <code>
 * Example.php
 * Example/
 *   Views/
 *     list.php
 *     item.php
 *     edit.php
 *   Locale/
 *     en_US.php
 *     pt_BR.php
 * </code>
 * 
 * Note that models are not included in the application itself; this is
 * for class-name deconfliction reasons.  Your models should be stored 
 * elsewhere in the Solar hierarchy, e.g. Example_Model_Name.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 */
abstract class Solar_Controller_Page extends Solar_Base {
    
    /**
     * 
     * User-defined configuration options.
     * 
     * Keys are:
     * 
     * : \\helper_class\\ : (array) An array of fallback helper classes.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'helper_class' => null,
    );
    
    /**
     * 
     * The default application action.
     * 
     * @var string
     * 
     */
    protected $_action_default = null;
    
    /**
     * 
     * The action being requested of (performed by) the application.
     * 
     * @var string
     * 
     */
    protected $_action = null;
    
    /**
     * 
     * Base directory under which actions, views, etc. are located.
     * 
     * @var string
     * 
     */
    protected $_dir = null;
    
    /**
     * 
     * Flash-messaging object.
     * 
     * @var Solar_Flash
     * 
     */
    protected $_flash;
    
    /**
     * 
     * Application request parameters collected from the URI pathinfo.
     * 
     * @var array
     * 
     */
    protected $_info = array();
    
    /**
     * 
     * The name of the layout to use for the view, minus the .layout.php suffix.
     * 
     * Default is 'twoColRight'.
     * 
     * @var string
     * 
     */
    protected $_layout = 'twoColRight';
    
    /**
     * 
     * Where the layout directory is located.
     * 
     * Default is 'Solar/App/Layouts/'.
     * 
     * @var string
     * 
     */
    protected $_layout_dir = 'Solar/App/Layouts/';
    
    /**
     * 
     * The name of the variable where page content is placed in the layout.
     * 
     * Default is 'layout_content'.
     * 
     * @var string
     * 
     */
    protected $_layout_var = 'layout_content';
    
    /**
     * 
     * The short-name of this application.
     * 
     * @var string
     * 
     */
    protected $_name;
    
    /**
     * 
     * Application request parameters collected from the URI query string.
     * 
     * @var string
     * 
     */
    protected $_query = array();
    
    /**
     * 
     * The name of the view to be rendered after the action.
     * 
     * @var string
     * 
     */
    protected $_view = null;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        $class = get_class($this);
        
        // auto-set the name; e.g. Solar_App_Something => 'something'
        if (empty($this->_name)) {
            $pos = strrpos($class, '_');
            $this->_name = strtolower(substr($class, $pos));
        }
        
        // auto-set the base directory, relative to the include path
        if (empty($this->_dir)) {
            // class-to-file conversion as an added directory
            $this->_dir = str_replace('_', '/', $class);
        }
        
        // fix the basedir
        $this->_dir = Solar::fixdir($this->_dir);
        
        // make sure we have a helper_class key; it might have been
        // left out of extended classes.
        if (empty($this->_config['helper_class'])) {
            $this->_config['helper_class'] = null;
        }
        
        // create the flash object
        $this->_flash = Solar::factory(
            'Solar_Flash',
            array('class' => $class)
        );
        
        // now do the parent construction
        parent::__construct($config);
        
        // extended setup
        $this->_setup();
    }
    
    /**
     * 
     * Try to force users to define what their view variables are.
     * 
     * @param string $key The property name.
     * 
     * @param mixed $val The property value.
     * 
     * @return void
     * 
     */
    public function __set($key, $val)
    {
        throw $this->_exception(
            'ERR_PROPERTY_NOT_DEFINED',
            array('property' => "\$$key")
        );
    }
    
    /**
     * 
     * Try to force users to define what their view variables are.
     * 
     * @param string $key The property name.
     * 
     * @return void
     * 
     */
    public function __get($key)
    {
        throw $this->_exception(
            'ERR_PROPERTY_NOT_DEFINED',
            array('property' => "\$$key")
        );
    }
    
    /**
     * 
     * Executes the requested action and returns its output with layout.
     * 
     * @param string $spec The action specification string, e.g.:
     * "tags/php+framework" or "user/pmjones/php+framework?page=3"
     * 
     * @return string The results of the action + view + layout.
     * 
     */
    public function fetch($spec = null)
    {
        // collect action, query, and info
        $this->_collect($spec);
        
        // run the pre-action code, forward to the first action (which
        // may trigger other actions), and run the post-action code.
        $this->_preAction();
        $this->_forward($this->_action, $this->_info);
        $this->_postAction();
        
        // get a view object and assign variables
        $view = $this->_newView();
        $view->assign($this);
        
        // are we using a layout?
        if (! $this->_layout) {
            
            // no layout, just render the view.
            return $view->fetch($this->_view . '.php');
            
        } else {
            
            // using a layout.
            $content = $view->fetch($this->_view . '.php');
            
            // re-use the same view object for the layout, adding the
            // layout path to the template path stack so it has 
            // preference.
            $view->addTemplatePath($this->_layout_dir);
            
            // return the page content inside the layout.
            $view->assign($this->_layout_var, $content);
            return $view->fetch($this->_layout . '.layout.php');
        }
    }
    
    /**
     * 
     * Executes the requested action and displays its output.
     * 
     * @param string $spec The action specification string, e.g.:
     * "tags/php+framework" or "user/pmjones/php+framework?page=3"
     * 
     * @return void
     * 
     */
    public function display($spec = null)
    {
        echo $this->fetch($spec);
    }
    
    /**
     * 
     * Creates and returns a new Solar_View object.
     * 
     * @return Solar_View
     * 
     */
    protected function _newView()
    {
        $class = get_class($this);
        $view = Solar::factory('Solar_View');
        
        // add the views for this page controller
        $view->addTemplatePath($this->_dir . 'Views/');
        
        // add helpers from user-defined locations, but give preference
        // to the helpers for this page controller.
        $view->addHelperClass($this->_config['helper_class']);
        $view->addHelperClass($class . '_Helper');
        
        // set the locale class for the getText helper
        $view->getTextRaw("$class::");
        
        // done!
        return $view;
    }
    
    /**
     * 
     * Hook for extended setup behaviors.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Collects action, pathinfo, and query values.
     * 
     * @param string $spec The action specification.
     * 
     * @return void
     * 
     */
    protected function _collect($spec)
    {
        // process the page/action/info specification
        if (! $spec) {
            
            // no spec, use the current URI
            $uri = Solar::factory('Solar_Uri_Action');
            $this->_info = $uri->path;
            $this->_query = $uri->query;
            
        } elseif ($spec instanceof Solar_Uri_Action) {
            
            // pull from a Solar_Uri_Action object
            $this->_info = $spec->path;
            $this->_query = $spec->query;
            
        } else {
            
            // a string, assumed to be a page/action/info?query spec.
            $uri = Solar::factory('Solar_Uri_Action');
            $uri->set($spec);
            $this->_info = $uri->path;
            $this->_query = $uri->query;
            
        }
        
        // remove the page name from the info
        if (! empty($this->_info[0]) && $this->_info[0] == $this->_name) {
            array_shift($this->_info);
        }
        
        // do we have an initial info element as an action method?
        if (! empty($this->_info[0])) {
            $method = $this->_actionMethod($this->_info[0]);
            if ($method) {
                // save it and remove from info
                $this->_action = array_shift($this->_info);
            }
        }
        
        // if no action yet, use the default
        if (! $this->_action) {
            $this->_action = $this->_action_default;
        }
    }
    
    /**
     * 
     * Executes just before the first action.
     * 
     * @return void
     * 
     */
    protected function _preAction()
    {
    }
    
    /**
     * 
     * Executes just after the last action, and just before the view.
     * 
     * @return void
     * 
     */
    protected function _postAction()
    {
    }
    
    /**
     * 
     * Retrieves the TAINTED value of a query request key by name.
     * 
     * Note that this value is direct user input; you should sanitize it
     * with Solar_Valid or Solar_Filter (or some other technique) before
     * using it.
     * 
     * @param string $key The query key.
     * 
     * @param mixed $val If the key does not exist, use this value
     * as a default in its place.
     * 
     * @return mixed The value of that query key.
     * 
     */
    protected function _query($key, $val = null)
    {
        if (array_key_exists($key, $this->_query) && $this->_query[$key] !== null) {
            return $this->_query[$key];
        } else {
            return $val;
        }
    }
    
    /**
     * 
     * Redirects to another page and action.
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @return void
     * 
     */
    protected function _redirect($spec)
    {
        if ($spec instanceof Solar_Uri_Action) {
            $href = $spec->fetch();
        } elseif (strpos($spec, '://') !== false) {
            // external link, protect against header injections
            $href = str_replace(array("\r", "\n"), '', $spec);
        } else {
            $uri = Solar::factory('Solar_Uri_Action');
            $href = $uri->quick($spec);
        }
        
        // make sure there's actually an href
        $href = trim($href);
        if (! $href || trim($spec) == '') {
            throw $this->_exception('ERR_REDIRECT_FAILED', array(
                'spec' => $spec,
                'href' => $href,
            ));
        }
        
        // kill off all output buffers and redirect
        while(@ob_end_clean());
        header("Location: $href");
        exit;
    }
    
    /**
     * 
     * Forwards internally to another action.
     * 
     * Note that this inserts the TAINTED user input from $this->_info
     * as the action method parameters; your action methods should
     * sanitize these values appropriately.
     * 
     * Also resets $this->_view to the requested action name.
     * 
     * @param string $action The action name.
     * 
     * @param array $params Parameters to pass to the action method.
     * 
     * @return void
     * 
     */
    protected function _forward($action, $params = null)
    {
        // does a related action-method exist?
        $method = $this->_actionMethod($action);
        if (! $method) {
            throw $this->_exception(
                'ERR_ACTION_NOT_FOUND',
                array(
                    'action' => $action,
                )
            );
        }
        
        // set the view to the most-recent action (this one ;-).
        // we do so before running the script so that the script
        // can override the view if needed.  essentially, just
        // drop the 'Action' suffix.
        $this->_view = substr($method, 0, -6);
        
        // run the action script, which may itself _forward() to
        // other actions.  pass all pathinfo parameters in order.
        if (empty($params)) {
            // speed boost
            return $this->$method();
        } else {
            // somewhat slower
            return call_user_func_array(
                array($this, $method),
                (array) $params
            );
        }
    }
    
    /**
     * 
     * Returns the method name for an action.
     * 
     * @param string $action The action name.
     * 
     * @return string The method name, or boolean false if the action
     * method does not exist.
     * 
     */
    protected function _actionMethod($action)
    {
        // convert example-name and example_name to ExampleName
        $method = str_replace(array('_', '-'), ' ', $action);
        $method = ucwords(trim($method));
        $method = str_replace(' ', '', $method);
        
        // convert ExampleName to exampleNameAction
        $method[0] = strtolower($method[0]);
        $method .= 'Action';
        
        // does it exist?
        if (method_exists($this, $method)) {
            return $method;
        } else {
            return false;
        }
    }
}
?>