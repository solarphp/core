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
 *   Actions/
 *     list.action.php
 *     item.action.php
 *     edit.action.php
 *   Views/
 *     list.view.php
 *     item.view.php
 *     edit.view.php
 *   Locale/
 *     en_US.php
 *     pt_BR.php
 * </code>
 * 
 * Note that models are not included in the application itself; this is
 * for class-name deconfliction reasons.  Your models should be stored 
 * elsewhere in the Solar hierarchy, e.g. Example_Model_Name.
 * 
 * Note also that the Public directory will need a symlink in the public
 * web directory.
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
     * The info variables to use for actions.
     * 
     * The format of this array is key-value pairs, where the key is
     * the action name, and the value is a slash-separated string of
     * $_info keys.  For example:
     * 
     * <code type="php">
     * $this->_action_info = array(
     *     'item' => 'id', // "item/:id"
     *     'list' => 'year/month', // "list/:year/:month"
     * );
     * </code>
     * 
     * You can then call $this->_info('id') for the 'item' action,
     * or $this->_info('year') and $this->_info('month') in the 'list'
     * action.
     * 
     * If you want default values, you can do this:
     * 
     * <code type="php">
     * $this->_action_info = array(
     *     'list' => 'year=2005/month=01',
     * );
     * </code>
     * 
     * @var string
     * 
     */
    protected $_action_info = array();
    
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
     * @var string
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
        
        // auto-set the locale directory
        if (empty($this->_config['locale'])) {
            $this->_config['locale'] = Solar::fixdir(
                $this->_dir . 'Locale/'
            );
        }
        
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
        $this->_forward($this->_action);
        $this->_postAction();
        
        // get a view object and assign variables
        $view = $this->_newView();
        $view->assign($this);
        
        // are we using a layout?
        if (! $this->_layout) {
            
            // no layout, just render the view.
            return $view->fetch($this->_view . '.view.php');
            
        } else {
            
            // using a layout.  render the view.
            $content = $view->fetch($this->_view . '.view.php');
            
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
            $info = $uri->path;
            $this->_query = $uri->query;
            
        } elseif ($spec instanceof Solar_Uri_Action) {
            
            // pull from a Solar_Uri_Action object
            $info = $spec->path;
            $this->_query = $spec->query;
            
        } else {
            
            // a string, assumed to be a page/action/info?query spec.
            $uri = Solar::factory('Solar_Uri_Action');
            $uri->set($spec);
            $info = $uri->path;
            $this->_query = $uri->query;
        }
        
        // remove the page name from the info
        if (! empty($info[0]) && $info[0] == $this->_name) {
            array_shift($info);
        }
        
        // do we have an initial info element?
        if (! empty($info[0])) {
            
            // look at it to see if it's a known action
            if (! empty($this->_action_info[$info[0]])) {
                
                // it's in the _action_info mapping; save it
                // and remove it from the info map.
                $this->_action = array_shift($info);
                
            } else {
                
                // not a mapped action; is it in the Actions/ directory?
                $file = $this->_actionFile($info[0]);
                if (Solar::fileExists($file)) {
                    // yes; save it, and remove from info map
                    $this->_action = array_shift($info);
                }
            }
        }
        
        // do we have an action yet?
        if (! $this->_action) {
            // no, so use the default
            $this->_action = $this->_action_default;
        }
        
        // move $info to $this->_info so we always have the originals
        $this->_info = $info;
        
        // import $this->_action_info mapped variables to $this->_info as well
        if (! empty($this->_action_info[$this->_action])) {
            $map = trim($this->_action_info[$this->_action], '/');
            $parts = explode('/', $map);
            foreach ($parts as $key => $val) {
                
                // 0 is the name, 1 is the default value
                $tmp = explode('=', $val);
                $tmp[0] = trim($tmp[0]);
                if (empty($tmp[1])) {
                    $tmp[1] = null;
                } else {
                    $tmp[1] = trim($tmp[1]);
                }
                
                // set the info value
                if (empty($info[$key])) {
                    // use default value
                    $this->_info[$tmp[0]] = $tmp[1];
                } else {
                    // user-provided value
                    $this->_info[$tmp[0]] = $info[$key];
                }
            }
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
     * Includes a file in an isolated scope (but with access to $this).
     * 
     * @param string The file to include.
     * 
     * @return mixed The return from the included file.
     * 
     */
    protected function _run()
    {
        return require func_get_arg(0);
    }
    
    /**
     * 
     * Retrieve the TAINTED value of a pathinfo request key by name.
     * 
     * Note that this value is direct user input; you should sanitize it with
     * Solar_Valid or Solar_Filter (or some other technique) before using it.
     * 
     * @param string $key The info key.
     * 
     * @param mixed $val If the key does not exist, use this value
     * as a default in its place.
     * 
     * @return mixed The value of that info key.
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
     * Note that this value is direct user input; you should sanitize it with
     * Solar_Valid or Solar_Filter (or some other technique) before using it.
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
     * @param string $action The action name.
     * 
     * @return void
     * 
     */
    protected function _forward($action)
    {
        // find the file
        $file = $this->_actionFile($action);
        if (! Solar::fileExists($file)) {
            throw $this->_exception(
                'ERR_ACTION_NOT_FOUND',
                array(
                    'action' => $action,
                )
            );
        }
        
        // set the view to the most-recent action (this one ;-).
        // we do so before running the script so that the script
        // can override the view if needed.  note that this means
        // the view dirs need to match the action dirs.  also,
        // replace dashes with slashes for subdirectories.
        $this->_view = str_replace('-', '/', $action);
        
        // run the action script, which may itself _forward() to
        // other actions.
        $this->_run($file);
    }
    
    /**
     * 
     * Returns the file name for an action.
     * 
     * @param string $action The action name.
     * 
     * @return string A file name for the action.
     * 
     */
    protected function _actionFile($action)
    {
        // filter the name so we don't get file traversals
        $file = preg_replace('/[^a-z0-9-]/i', '', $action);
        
        // convert dashes to slashes;
        // e.g., foo-bar-baz => foo/bar/baz.action.php
        $file = str_replace('-', '/', $file) . '.action.php';
        
        // done
        return $this->_dir . "Actions/$file";
    }
}
?>