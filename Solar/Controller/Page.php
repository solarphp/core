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
 * Abstract page controller class.
 * 
 * Expects a directory structure similar to the following:
 * 
 * <code>
 * Vendor/              # your vendor namespace
 *   App/               # subdirectory for page controllers
 *     Helper/          # shared helper classes
 *       ...
 *     Layout/          # shared layout files
 *       ...                                 
 *     Locale/          # shared locale files
 *       ...                                 
 *     View/            # shared view scripts
 *       ...
 *     Example.php      # an example page controller app
 *     Example/
 *       Helper/        # helper classes specific to the page
 *         ...
 *       Layout/        # layout files to override shared layouts
 *         ...
 *       Locale/        # locale files
 *         en_US.php
 *         pt_BR.php
 *       View/          # view scripts
 *         _item.php    # partial template
 *         list.php     # full template
 *         edit.php
 * </code>
 * 
 * Note that models are not included in the application itself; this is
 * for class-name deconfliction reasons.  Your models should be stored 
 * elsewhere in the Solar hierarchy, e.g. Vendor_Model_Name.
 * 
 * When you call Solar_Controller_Page::fetch(), these intercept methods
 * are run in the following order:
 * 
 * * Solar_Controller_Page::_load() to load class properties from the 
 *   fetch() URI specification
 * 
 * * Solar_Controller_Page::_preRun() before the first action
 * 
 * * Solar_Controller_Page::_preAction() before each action (including
 *   _forward()-ed actions)
 * 
 * * ... The action method itself runs here ...
 * 
 * * Solar_Controller_Page::_postAction() after each action
 * 
 * * Solar_Controller_Page::_postRun() after the last action, and before
 *   rendering
 * 
 * * Solar_Controller_Page::_render() to render the view and layout; 
 *   this in its turn calls Solar_Controller_Page::_viewInstance() for 
 *   the view object, and Solar_Controller_Page::_layoutInstance() for 
 *   the layout object.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 */
abstract class Solar_Controller_Page extends Solar_Base {
    
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
     * The name of the layout to use for the view, minus the .php suffix.
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
     * Default is 'Solar/App/Layout/'.
     * 
     * @var string
     * 
     */
    protected $_layout_dir = 'Solar/App/Layout/';
    
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
     * The name of the view to be rendered after all actions.
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
        // load action, info, and query properties
        $this->_load($spec);
        
        // prerun hook
        $this->_preRun();
        
        // actions
        $this->_forward($this->_action, $this->_info);
        
        // postrun hook
        $this->_postAction();
        
        // return a rendered view
        return $this->_render();
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
     * Renders the view based on page properties.
     * 
     * @return string The results of the action + view + layout.
     * 
     */
    protected function _render()
    {
        // get a view object and assign variables
        $view = $this->_viewInstance();
        $view->assign($this);
        
        // are we using a layout?
        if (! $this->_layout) {
            
            // no layout, just render the view.
            return $view->fetch($this->_view . '.php');
            
        } else {
            
            // using a layout. execute the view and retain the output.
            $output = $view->fetch($this->_view . '.php');
            
            // get a layout object and assign properties of the view
            $layout = $this->_layoutInstance();
            $layout->assign($view);
            
            // assign the view output and render the layout
            $layout->assign($this->_layout_var, $output);
            return $layout->fetch($this->_layout . '.php');
            
        }
    }
    
    /**
     * 
     * Creates and returns a new Solar_View object for a view.
     * 
     * Automatically sets up a template-path stack for you, searching
     * for view files in this order:
     * 
     * # Vendor/App/Example/View/
     * 
     * # Vendor/App/View
     * 
     * Automatically sets up a helper-class stack for you, searching
     * for helper classes in this order:
     * 
     * # Vendor_App_Example_Helper_
     * 
     * # Vendor_App_Helper_
     * 
     * # Vendor_View_Helper_
     * 
     * # Solar_View_Helper_ (this is part of Solar_View to begin with)
     * 
     * @return Solar_View
     * 
     */
    protected function _viewInstance()
    {
        $view = Solar::factory('Solar_View');
        $class = get_class($this);
        
        // stack of helper classes
        $helper = array();
        
        // stack of template paths
        $template = array();
        
        // find the class-level templates (Vendor/App/Example/View)
        $template[] = $this->_dir . 'View';
        
        // find the vendor-level templates (Vendor/App/View)
        $template[] = dirname($this->_dir) . DIRECTORY_SEPARATOR . 'View';
        
        // add the template paths to the view object.
        // the order of searching will be:
        // Vendor/App/Example/View, Vendor/App/View
        $view->addTemplatePath($template);
        
        // find the class-level helpers (Vendor_App_Example_Helper)
        $helper[] = $class . '_Helper';
        
        // find the parent-level helpers (Vendor_App_Helper)
        $pos = strrpos($class, '_');
        $helper[] = substr($class, 0, -$pos) . '_Helper';
        
        // find the vendor-level helpers (Vendor_View_Helper)
        $pos = strpos($class, '_');
        $vendor = substr($class, 0, $pos);
        if ($vendor != 'Solar') {
            $helper[] = $vendor . '_View_Helper';
        }
        
        // add the helper classes to the view object.
        // the order of searching will be:
        // Vendor_App_Example_Helper_*, Vendor_App_Helper_*,
        // Vendor_View_Helper_*, Solar_View_Helper_*
        $view->addHelperClass($helper);
        
        // set the locale class for the getText helper
        $view->getTextRaw("$class::");
        
        // done!
        return $view;
    }
    
    /**
     * 
     * Creates and returns a new Solar_View object for a layout.
     * 
     * Automatically sets up a template-path stack for you, searching
     * for view files in this order:
     * 
     * # Vendor/App/Example/Layout/
     * 
     * # Vendor/App/Layout
     * 
     * Automatically sets up a helper-class stack for you, searching
     * for helper classes in this order:
     * 
     * # Vendor_App_Helper_
     * 
     * # Vendor_View_Helper_
     * 
     * # Solar_View_Helper_ (this is part of Solar_View to begin with)
     * 
     * @return Solar_View
     * @return Solar_View
     * 
     */
    protected function _layoutInstance()
    {
        $view = Solar::factory('Solar_View');
        $class = get_class($this);
        
        // stack of helper classes
        $helper = array();
        
        // stack of template paths
        $template = array();
        
        // find the class-level templates (Vendor/App/Example/Layout)
        $template[] = $this->_dir . 'Layout';
        
        // find the vendor-level templates (Vendor/App/Layout)
        $template[] = dirname($this->_dir) . DIRECTORY_SEPARATOR . 'Layout';
        
        // add the template paths to the view object
        $view->addTemplatePath($template);
        
        // find the class-level helpers (Vendor_App_Example_Helper)
        $helper[] = $class . '_Helper';
        
        // find the parent-level helpers (Vendor_App_Helper)
        $pos = strrpos($class, '_');
        $helper[] = substr($class, 0, -$pos) . '_Helper';
        
        // find the vendor-level helpers (Vendor_View_Helper)
        $pos = strpos($class, '_');
        $vendor = substr($class, 0, $pos);
        if ($vendor != 'Solar') {
            $helper[] = $vendor . '_View_Helper';
        }
        
        // add the helper class names to the view object
        $view->addHelperClass($helper);
        
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
     * Loads properties from an action specification.
     * 
     * @param string $spec The action specification.
     * 
     * @return void
     * 
     */
    protected function _load($spec)
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
            $method = $this->_getActionMethod($this->_info[0]);
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
     * Executes after collection but before the first action.
     * 
     */
    protected function _preRun()
    {
    }
    
    /**
     * 
     * Executes just before each action.
     * 
     * @return void
     * 
     */
    protected function _preAction()
    {
    }
    
    /**
     * 
     * Executes just after each action.
     * 
     * @return void
     * 
     */
    protected function _postAction()
    {
    }
    
    /**
     * 
     * Executes after the last action and before rendering.
     * 
     */
    protected function _postRun()
    {
    }
    
    /**
     * 
     * Retrieves the TAINTED value of a path-info value by position.
     * 
     * Note that this value is direct user input; you should sanitize it
     * with Solar_Valid or Solar_Filter (or some other technique) before
     * using it.
     * 
     * @param int $key The path-info position number.
     * 
     * @param mixed $val If the key does not exist, use this value
     * as a default in its place.
     * 
     * @return mixed The value of that query key.
     * 
     */
    protected function _info($key, $val = null)
    {
        if (array_key_exists($key, $this->_info) && $this->_info[$key] !== null) {
            return $this->_info[$key];
        } else {
            return $val;
        }
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
        $method = $this->_getActionMethod($action);
        if (! $method) {
            throw $this->_exception(
                'ERR_ACTION_NOT_FOUND',
                array(
                    'action' => $action,
                )
            );
        }
        
        // set the view to the requested action
        $this->_view = $this->_getActionView($action);
        
        // run this before every action
        $this->_preAction();
        
        // run the action script, which may itself _forward() to
        // other actions.  pass all pathinfo parameters in order.
        if (empty($params)) {
            // speed boost
            $this->$method();
        } else {
            // somewhat slower
            call_user_func_array(
                array($this, $method),
                (array) $params
            );
        }
        
        // run this after every action
        $this->_postAction();
        
        // done!
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
    protected function _getActionMethod($action)
    {
        // convert example-name and example_name to "actionExampleName"
        $word = str_replace(array('_', '-'), ' ', $action);
        $word = ucwords(trim($word));
        $word = 'action' . str_replace(' ', '', $word);
        
        // does it exist?
        if (method_exists($this, $word)) {
            return $word;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns the view name for an action.
     * 
     * @param string $action The action name.
     * 
     * @return string The related view name.
     * 
     */
    protected function _getActionView($action)
    {
        // convert example-name and example_name to exampleName
        $word = str_replace(array('_', '-'), ' ', $action);
        $word = ucwords(trim($word));
        $word = str_replace(' ', '', $word);
        $word[0] = strtolower($word[0]);
        return $word;
    }
}
?>