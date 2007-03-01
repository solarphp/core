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
 * Expects a directory structure similar to the following ...
 *
 *     Vendor/              # your vendor namespace
 *       App/               # subdirectory for page controllers
 *         Helper/          # shared helper classes
 *           ...
 *         Layout/          # shared layout files
 *           ...
 *         Locale/          # shared locale files
 *           ...
 *         View/            # shared view scripts
 *           ...
 *         Example.php      # an example page controller app
 *         Example/
 *           Helper/        # helper classes specific to the page
 *             ...
 *           Layout/        # layout files to override shared layouts
 *             ...
 *           Locale/        # locale files
 *             en_US.php
 *             pt_BR.php
 *           View/          # view scripts
 *             _item.php    # partial template
 *             list.php     # full template
 *             edit.php
 *
 * Note that models are not included in the application itself; this is
 * for class-name deconfliction reasons.  Your models should be stored
 * elsewhere in the Solar hierarchy, for example Vendor_Model_Name.
 *
 * When you call [[fetch()]], these intercept methods
 * are run in the following order ...
 *
 * * [[_load()]] to load class properties from the
 *   fetch() URI specification
 *
 * * [[_preRun()]] before the first action
 *
 * * [[_preAction()]] before each action (including
 *   _forward()-ed actions)
 *
 * * ... The action method itself runs here ...
 *
 * * [[_postAction()]] after each action
 *
 * * [[_postRun()]] after the last action, and before rendering
 *
 * * [[_render()]] to render the view and layout;
 *   this in its turn calls [[_getView()]] for
 *   the view object, and [[_setViewLayout()]] to
 *   reset the view object to use layout templates.
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
     * Tells which alternative output formats are supported by which actions.
     * 
     * Array format is 'action' => array('format', 'format', 'format').
     * 
     * If an action is not listed, it will not respond to alternative formats.
     * 
     * For example ...
     * 
     * {{code: php
     *     $_action_format = array(
     *         // multiple formats
     *         'browse' => array('xml', 'rss', 'atom')
     *         // shorthand for just one format
     *         'read'   => 'xml',
     *     );
     * }}
     * 
     * @var array
     * 
     */
    protected $_action_format = array();
    
    /**
     *
     * Session data, including read-once flashes.
     *
     * @var Solar_Session
     *
     */
    protected $_session;

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
     * The name of the layout to be rendered.
     *
     * @var string
     *
     */
    protected $_layout = null;

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
    protected $_name = null;

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
     * Name of the form element that holds the process request value (such as
     * 'Save', 'Next', 'Cancel', etc)
     * 
     * Default is 'process', as in $_POST['process'] or $_GET['process'].
     * 
     * @var string
     *
     * @see Solar_Controller_Page::_process()
     *
     */
    protected $_process_key = 'process';

    /**
     *
     * The name of the view to be rendered.
     *
     * @var string
     *
     */
    protected $_view = null;
    
    /**
     * 
     * Use this output format for views.
     * 
     * For example, say the action is "read". In the default case, the format
     * is empty, so  the _render() method will look for a view named 
     * "read.php". However, if the format is "xml", the _render() method will
     * look for a view named "read.xml.php".
     * 
     * Has no effect on the layout script that _render() looks for.
     * 
     * @var string
     * 
     */
    protected $_format = null;
    
    /**
     *
     * Request environment details: get, post, etc.
     *
     * @var Solar_Request
     *
     */
    protected $_request;
    
    /**
     * 
     * These helper classes will be added in the middle of the stack, between the
     * Solar_View_Helper final fallbacks and the vendor+app specific helpers.
     * 
     * @var array
     * 
     */
    protected $_helper_class = array();
    
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

        // create the request object
        $this->_request = Solar::factory('Solar_Request');

        // auto-set the name; for example Solar_App_Something => 'something'
        if (empty($this->_name)) {
            $pos = strrpos($class, '_');
            $this->_name = substr($class, $pos + 1);
            $this->_name[0] = strtolower($this->_name[0]);
        }

        // create the flash object
        $this->_session = Solar::factory(
            'Solar_Session',
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
     * @param string $spec The action specification string, for example,
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

        // action chain, with pre- and post-action hooks
        $this->_forward($this->_action, $this->_info);

        // postrun hook
        $this->_postRun();

        // render the view and layout, with pre- and post-render hooks
        return $this->_render();
    }

    /**
     *
     * Executes the requested action and displays its output.
     *
     * @param string $spec The action specification string, for example,
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
     * Renders the view with layout with pre- and post-rendering.
     *
     * @return string The results of the view and layout scripts.
     *
     */
    protected function _render()
    {
        // get a view object and assign variables
        $view = $this->_getView();
        $this->_preRender($view);
        $view->assign($this);
        $output = null;
        
        // are we using a view?
        if ($this->_view) {
            try {
                // set the template name from the view and format
                $tpl = $this->_view
                     . ($this->_format ? ".{$this->_format}" : "")
                     . ".php";
                // get the view output
                $output = $view->fetch($tpl);
            } catch (Solar_View_Exception_TemplateNotFound $e) {
                $view->errors[] = $this->locale('ERR_VIEW_NOT_FOUND');
                $view->errors[] = implode(PATH_SEPARATOR, $e->getInfo('path'));
                $view->errors[] = $e->getInfo('name');
                $output = $view->fetch('error.php');
            }
        }
        
        // are we using a layout?
        if ($this->_layout) {
            try {
                // reset the view object to use layout templates.
                $this->_setViewLayout($view);
            
                // assign the view output
                $view->assign($this->_layout_var, $output);
            
                // set the template name from the layout value
                $tpl = $this->_layout . ".php";
                
                // get the layout output
                $output = $view->fetch($tpl);
            } catch (Solar_View_Exception_TemplateNotFound $e) {
                $view->errors[] = $this->locale('ERR_LAYOUT_NOT_FOUND');
                $view->errors[] = implode(PATH_SEPARATOR, $e->getInfo('path'));
                $view->errors[] = $e->getInfo('name');
                $output = $view->fetch('error.php');
            }
        }
        
        // apply post-render processing, and done
        return $this->_postRender($output);
    }

    /**
     *
     * Creates and returns a new Solar_View object for a view.
     *
     * Automatically sets up a template-path stack for you, searching
     * for view files in this order ...
     *
     * 1. Vendor/App/Example/View/
     *
     * 2. Vendor/App/View
     *
     * Automatically sets up a helper-class stack for you, searching
     * for helper classes in this order ...
     *
     * 1. Vendor_App_Example_Helper_
     *
     * 2. Vendor_App_Helper_
     *
     * 3. Vendor_View_Helper_
     *
     * 4. Solar_View_Helper_ (this is part of Solar_View to begin with)
     *
     * @return Solar_View
     *
     */
    protected function _getView()
    {
        $view = Solar::factory('Solar_View');

        // get the current class
        $class = get_class($this);

        // get the parent-level class
        $pos = strrpos($class, '_');
        $parent = substr($class, 0, $pos);

        // who's the vendor?
        $pos = strpos($class, '_');
        $vendor = substr($class, 0, $pos);

        // add template paths to the view object.
        // the order of searching will be:
        // Vendor/App/Example/View, Vendor/App/View, Solar/App/View
        $template = array();
        $template[] = str_replace('_', DIRECTORY_SEPARATOR, "{$class}_View");
        $template[] = str_replace('_', DIRECTORY_SEPARATOR, "{$parent}_View");
        if ($vendor != 'Solar') {
            // non-Solar vendor, add Solar views as final fallback
            $template[] = str_replace('_', DIRECTORY_SEPARATOR, 'Solar_App_View');
        }
        $view->addTemplatePath($template);

        // add helper classes to the view object.
        // the order of searching will be:
        // Vendor_App_Example_Helper_*, Vendor_App_Helper_*,
        // Vendor_View_Helper_*, Solar_View_Helper_*
        $helper = array();
        $helper[] = $class . '_Helper';
        $helper[] = $parent . '_Helper';
        $helper[] = $vendor . '_View_Helper';
        
        // are there additional helper classes we need to add?
        if (! empty($this->_helper_class)) {
            $helper = array_merge($helper, (array) $this->_helper_class);
        }
        
        $view->addHelperClass($helper);

        // set the locale class for the getText helper
        $view->getHelper('getTextRaw')->setClass($class);

        // done!
        return $view;
    }

    /**
     *
     * Points an existing Solar_View object to the Layout templates.
     *
     * This effectively re-uses the Solar_View object from the page
     * (with its helper objects and data) to build the layout.  This
     * helps to transfer JavaScript and other layout data back up to
     * the layout with zero effort.
     *
     * Automatically sets up a template-path stack for you, searching
     * for layout files in this order ...
     *
     * 1. Vendor/App/Example/Layout/
     *
     * 2. Vendor/App/Layout/
     *
     * 3. Solar/App/Layout/
     *
     * @param Solar_View $view The Solar_View object to modify.
     *
     * @return Solar_View
     *
     */
    protected function _setViewLayout($view)
    {
        // get the current class
        $class = get_class($this);

        // get the parent-level class
        $pos = strrpos($class, '_');
        $parent = substr($class, 0, $pos);

        // who's the vendor?
        $pos = strpos($class, '_');
        $vendor = substr($class, 0, $pos);

        // reset template paths in the view object.
        // the order of searching will be:
        // Vendor/App/Example/Layout, Vendor/App/Layout, Solar/App/Layout
        $template = array();
        $template[] = str_replace('_', DIRECTORY_SEPARATOR, "{$class}_Layout");
        $template[] = str_replace('_', DIRECTORY_SEPARATOR, "{$parent}_Layout");
        if ($vendor != 'Solar') {
            // non-Solar vendor, add Solar views as final fallback
            $template[] = str_replace('_', DIRECTORY_SEPARATOR, 'Solar_App_Layout');
        }
        $view->setTemplatePath($template);
    }

    /**
     *
     * Loads properties from an action specification.
     * 
     * Note that if the action info ends in a format extension, layout will
     * automatically be turned off.
     * 
     * For example, "foo/bar/baz.xml" will set $this->_format = "xml" and
     * $this->_layout = null.
     * 
     * @param string $spec The action specification.
     *
     * @return void
     *
     */
    protected function _load($spec)
    {
        // process the action/param.format?query specification
        if (! $spec) {

            // no spec, use the current URI
            $uri = Solar::factory('Solar_Uri_Action');
            $this->_info = $uri->path;
            $this->_query = $uri->query;
            $this->_format = $uri->format;
            
        } elseif ($spec instanceof Solar_Uri_Action) {

            // pull from a Solar_Uri_Action object
            $this->_info = $spec->path;
            $this->_query = $spec->query;
            $this->_format = $spec->format;
            
        } else {

            // a string, assumed to be an action/param.format?query spec.
            $uri = Solar::factory('Solar_Uri_Action');
            $uri->set($spec);
            $this->_info = $uri->path;
            $this->_query = $uri->query;
            $this->_format = $uri->format;
            
        }
        
        // ignore .php formats
        if (strtolower($this->_format) == 'php') {
            $this->_format = '';
        }
        
        // if the first param is the page name, drop it.
        // needed when no spec is passed and we're using the default URI.
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
        
        // are we asking for a non-default format?
        if ($this->_format) {
            
            // what formats does the action allow?
            $action_format = empty($this->_action_format[$this->_action])
                  ? array()
                  : (array) $this->_action_format[$this->_action];
        
            // does the action support the requested format?
            if (in_array($this->_format, $action_format)) {
                // action supports the format; turn off layouts by default.
                $this->_layout = null;
            } else {
                // action does not support the format.
                // add the format extension to the last param.
                // that's because it might be an actual file name.
                $val = end($this->_info);
                $key = key($this->_info);
                $this->_info[$key] = $val . '.' . $this->_format;
                // discard the unrecognized format.
                $this->_format = null;
            }
        }
        
        // finally, remove blank info elements from the end.
        // this happens sometimes with elements being added
        // and removed from format checking, and helps make
        // sure that action default parameters are honored.
        $i = count($this->_info);
        while ($i --) {
            if (! empty($this->_info[$i])) {
                // not empty, stop removing blanks
                break;
            } else {
                unset($this->_info[$i]);
            }
        }
    }

    /**
     *
     * Retrieves the TAINTED value of a path-info parameter by position.
     *
     * Note that this value is direct user input; you should sanitize it
     * with Solar_Valid or Solar_Filter (or some other technique) before
     * using it.
     *
     * @param int $key The path-info parameter position.
     *
     * @param mixed $val If the position does not exist, use this value
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
     * Forwards internally to another action, using pre- and post-
     * action hooks, and resets $this->_view to the requested action.
     *
     * You should generally use "return $this->_forward(...)" instead
     * of just $this->_forward; otherwise, script execution will come
     * back to where you called the forwarding.
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
        // set the current action on entry
        $this->_action = $action;

        // run this before every action, may change the
        // requested action.
        $this->_preAction();

        // does a related action-method exist?
        $method = $this->_getActionMethod($this->_action);
        if (! $method) {
            throw $this->_exception(
                'ERR_ACTION_NOT_FOUND',
                array(
                    'action' => $this->_action,
                )
            );
        }

        // set the view to the requested action
        $this->_view = $this->_getActionView($this->_action);

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

        // set the current action on exit so that $this->_action is
        // always the **first** action requested when we finally exit.
        $this->_action = $action;
    }

    /**
     *
     * Whether or not user requested a specific process within the action.
     *
     * By default, looks for $process_key in [[Solar_Request::post()]] to get the
     * value of the process request.
     *
     * Checks against "PROCESS_$type" locale string for matching.  For example,
     * $this->_isProcess('save') checks Solar_Request::post('process') 
     * against $this->locale('PROCESS_SAVE').
     *
     * @param string $type The process type; for example, 'save', 'delete',
     * 'preview', etc.  If empty, returns true if *any* submission type
     * was posted.
     *
     * @param string $process_key If not empty, check against this
     * [[Solar_Request::post()]] key instead $this->_process_key. Default
     * null.
     *
     * @return bool
     *
     */
    protected function _isProcess($type = null, $process_key = null)
    {   
        // make sure we know what post-var to look in
        if (empty($process_key)) {
            $process_key = $this->_process_key;
        }
        
        // didn't ask for a submission type; answer if *any* submission
        // was attempted.
        if (empty($type)) {
            $any = $this->_request->post($process_key);
            return ! empty($any);
        }
        
        // asked for a submission type, find the locale string for it.
        $locale_key = 'PROCESS_' . strtoupper($type);
        $locale = $this->locale($locale_key);

        // $process must be non-empty, and must match locale string.
        // not enough just to match the locale string, as it might
        // be empty.
        $process = $this->_request->post($process_key, false);
        return $process && $process == $locale;
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


    // -----------------------------------------------------------------
    //
    // Behavior hooks.
    //
    // -----------------------------------------------------------------


    /**
     *
     * Executes after construction.
     *
     * @return void
     *
     */
    protected function _setup()
    {
    }

    /**
     *
     * Executes before the first action.
     * 
     * @return void
     * 
     */
    protected function _preRun()
    {
    }

    /**
     *
     * Executes before each action.
     *
     * @return void
     *
     */
    protected function _preAction()
    {
    }

    /**
     *
     * Executes after each action.
     *
     * @return void
     *
     */
    protected function _postAction()
    {
    }

    /**
     *
     * Executes after the last action.
     *
     * @return void
     *
     */
    protected function _postRun()
    {
    }

    /**
     *
     * Executes before rendering the page view and layout.
     *
     * Use this to pre-process the Solar_View object, or to manipulate
     * controller properties with view helpers.
     *
     * @param Solar_View $view The Solar_View object for rendering the
     * page view script.
     *
     * @return void
     *
     */
    protected function _preRender($view)
    {
    }

    /**
     *
     * Executes after rendering the page view and layout.
     *
     * Use this to do a final filter or maniuplation of the output text
     * from the view and layout scripts.  By default, it leaves the
     * rendered output alone and returns it as-is.
     *
     * @param string $output The output from the rendered view and layout.
     *
     * @return string The filtered output.
     *
     */
    protected function _postRender($output)
    {
        return $output;
    }
}
