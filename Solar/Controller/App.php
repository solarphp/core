<?php
/**
 * 
 * Abstract application controller class for Solar.
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
 * Load Solar_Uri for dispatch comparisons.
 */
Solar::loadClass('Solar_Uri');

/**
 * 
 * Abstract application (page) controller class for Solar.
 * 
 * Expects a directory structure like this example:
 * 
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
 *   Public/
 *     stylesheet.css
 *     javascript.js
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
abstract class Solar_Controller_App extends Solar_Base {
    
    /**
     * 
     * Base directory under which actions, views, etc. are located.
     * 
     * @access protected
     * 
     * @var string
     * 
     */
    protected $_basedir = null;
    
    /**
     * 
     * The default application action.
     * 
     * @access protected
     * 
     * @var string
     * 
     */
    protected $_default_action = null;
    
    /**
     * 
     * Application request parameters collected from the URI pathinfo.
     * 
     * @access protected
     * 
     * @var string
     * 
     */
    protected $_info = array();
    
    /**
     * 
     * Application request parameters collected from the URI query string.
     * 
     * @access protected
     * 
     * @var string
     * 
     */
    protected $_query = array();
    
    /**
     * 
     * The action being requested of (performed by) the application.
     * 
     * @access protected
     * 
     * @var string
     * 
     */
    protected $_action = null;
    
    /**
     * 
     * The name of the view to be rendered after the action.
     * 
     * @access protected
     * 
     * @var string
     * 
     */
    protected $_view = null; // the requested view
    
    /**
     * 
     * Data to be passed up to the site layout.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_layout = array();
    
    /**
     * 
     * The dispatch pathinfo variable map.
     * 
     * The format of this array is key-value pairs, where the key is
     * the action name, and the value is a sequential array of
     * variable names in pathinfo positions.  For example, see this
     * $_map array:
     * 
     * $_map = array(
     *     'item' => array('id'), // "item/:id"
     *     'list' => array('year', 'month') // "list/:year/:month"
     * );
     * 
     * @access protected
     * 
     * @var string
     * 
     */
    protected $_map = array();
    
    /**
     * 
     * Constructor.
     * 
     * @access public
     * 
     */
    public function __construct($config = null)
    {
        // auto-set the base directory
        if (empty($this->_basedir)) {
            // remove Solar/Controller/App.php from the __FILE__
            // so that we have a base prefix
            $base = substr(__FILE__, 0, -24);
            
            // class-to-file conversion as an added directory
            $dir = str_replace('_', '/', get_class($this));
            
            // done
            $this->_basedir = $base . $dir;
        }
        
        // fix the basedir
        $this->_basedir = Solar::fixdir($this->_basedir);
        
        // auto-set the locale directory
        if (empty($this->_config['locale'])) {
            $this->_config['locale'] = Solar::fixdir(
                $this->_basedir . 'Locale/'
            );
        }
        
        // now do the parent construction
        parent::__construct($config);
    }
    
    /**
     * 
     * Try to force users to define what their view variables are. :-(
     * 
     * @access public
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
        throw new Exception("property '$key' not defined");
    }
    
    /**
     * 
     * Try to force users to define what their view variables are. :-(
     * 
     * @access public
     * 
     * @param string $key The property name.
     * 
     * @param mixed $val The property value.
     * 
     * @return void
     * 
     */
    public function __get($key)
    {
        throw new Exception("property '$key' not defined");
    }
    
    /**
     * 
     * Execute the requested action and returns its output.
     * 
     * @access public
     * 
     * @param string $spec The action specification string, e.g.:
     * "tags/php+framework" or "user/pmjones/php+framework?page=3"
     * 
     * @return string The results of the action + view execution.
     * 
     */
    public function fetch($spec = null)
    {
        // forward to the proper action.
        // this uses the current collection spec,
        // but allows for forwarding from within
        // the action.
        $this->_forward($spec);
        
        // set up a view object
        $tpl = Solar::factory('Solar_Template');
        
        // add the app-specific path for views
        $tpl->addPath('template', $this->_basedir . 'Views/');
        
        // add the app-specific path for view helpers (Savant plugins)
        $tpl->addPath('resource', $this->_basedir . 'Helpers/');
        
        // tell the template view what locale strings to use
        $class = get_class($this);
        $tpl->locale("$class::");
        
        // set the view template script
        if (! $this->_view) {
            // use the most-recent action name
            $this->_view = $this->_action;
        }
        
        // assign the app data, run the view, return the output
        $tpl->assign($this);
        $result = $tpl->fetch($this->_view . '.view.php');
        return $result;
    }
    
    /**
     * 
     * Executes the requested action and displays its output.
     * 
     * @access public
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
     * Retrieve the value of a pathinfo request key by name.
     * 
     * @access protected
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
        if (empty($this->_info[$key])) {
            return $val;
        } else {
            return $this->_info[$key];
        }
    }
    
    /**
     * 
     * Retrieve they value of a query request key by name.
     * 
     * @access protected
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
        if (empty($this->_query[$key])) {
            return $val;
        } else {
            return $this->_query[$key];
        }
    }
    
    /**
     * 
     * Redirects to another URL.
     * 
     * @access public
     * 
     * @param string $url The URL to redirect to.
     * 
     * @return void
     * 
     */
    protected function _redirect($url)
    {
        // protect against header injections
        $url = str_replace(array("\r", "\n"), '', $url);
        
        // kill off any output buffers
        ob_clean();
        
        // redirect, and exit all remaining scripts
        header("Location: $url");
        exit;
    }
    
    /**
     * 
     * Executes an action.
     * 
     * @access public
     * 
     * @param string $spec The action specification.
     * 
     * @return string The view template the action expects to use.
     * 
     */
    protected function _forward($spec = null)
    {
        // collect the action, info, and query values
        $this->_collect($spec);
        
        // what is the action name?
        // filter it so we don't get file traversals,
        // then convert to a script filename.
        $name = $this->_action;
        $name = preg_replace('/[^a-z0-9_\/]/i', '', $name);
        $file = $this->_basedir . "Actions/$name.action.php";
        
        // if the script doesn't exist, use the default.
        if (! $name || ! is_readable($file)) {
            $name = $this->_default_action;
            $name = preg_replace('/[^a-z0-9_\/]/i', '', $name);
            $file = $this->_basedir . "Actions/$name.action.php";
        }
        
        // run the action script
        $this->_run($file);
    }
    
    /**
     * 
     * Collects action, pathinfo, and query values.
     * 
     * @access protected
     * 
     * @param string $spec The action specification.
     * 
     * @return void
     * 
     */
    protected function _collect($spec = null)
    {
        // if the spec is null, use current URI
        if (! $spec) {
        
            $uri = Solar::factory('Solar_Uri');
            $this->_info = $uri->info;
            $this->_query = $uri->query;
            
        } elseif ($spec instanceof Solar_Uri) {
            
            $this->_info = $spec->info;
            $this->_query = $spec->query;
            
        } else {
            
            // it's a string. fake the scheme, domain, and path for the uri parser.
            $uri = Solar::factory('Solar_Uri');
            $uri->import('http://example.com/index.php/' . ltrim($spec, '/'));
            $this->_info = $uri->info;
            $this->_query = $uri->query;
        }
        
        // find the requested action
        $this->_action = array_shift($this->_info);
        if (! $this->_action) {
            $this->_action = $this->_default_action;
        }
        
        // if there is no map for this action, we're done
        if (empty($this->_map[$this->_action])) {
            // no need to map variable names
            return;
        }
        
        // track the numeric position for each mapping
        $i = 0;
        
        // go through the info map for the action
        foreach ($this->_map[$this->_action] as $key => $val) {
            
            // if the name is an integer, there is no default value.
            // thus, the value is itself the name.
            if (is_int($key)) {
                // use null as the default value
                $this->_info[$val] = (empty($this->_info[$i]) ? null : $this->_info[$i]);
            } else {
                // use $val as the default value
                $this->_info[$key] = (empty($this->_info[$i]) ? $val : $this->_info[$i]);
            }
            
            // advance to the next info position
            $i++;
        }
    }
    
    /**
     * 
     * Includes a file in an isolated scope (but with access to $this).
     * 
     * @access protected
     * 
     * @param string The file to include.
     * 
     * @return mixed The return from the included file.
     * 
     */
    protected function _run()
    {
        return include func_get_arg(0);
    }
}
?>