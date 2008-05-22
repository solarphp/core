<?php
/**
 * 
 * Abstract page controller class.
 * 
 * Expects a directory structure similar to the following ...
 * 
 *     Vendor/              # your vendor namespace
 *       App/               # subdirectory for page controllers
 *         Base/
 *           Helper/        # shared helper classes
 *           Layout/        # shared layout files
 *           Locale/        # shared locale files
 *           Model/         # shared model classes
 *           View/          # shared view scripts
 *         Example.php      # an example app
 *         Example/
 *           Helper/        # helper classes specific to the app
 *             ...
 *           Layout/        # layout files to override shared layouts
 *             ...
 *           Locale/        # locale files
 *             en_US.php
 *             pt_BR.php
 *           View/          # view scripts
 *             _item.php    # partial template
 *             list.php     # full template
 *             edit.php     # another full template
 * 
 * 
 * When you call [[fetch()]], these intercept methods are run in the
 * following order ...
 * 
 * 1. [[_load()]] to load class properties from the fetch() URI specification
 * 
 * 2. [[_preRun()]] before the first action
 * 
 * 3. [[_preAction()]] before each action (including _forward()-ed actions)
 * 
 * 4. ... The action method itself runs here ...
 * 
 * 5. [[_postAction()]] after each action
 * 
 * 6. [[_postRun()]] after the last action, and before rendering
 * 
 * 7. [[_render()]] to render the view and layout; this in its turn calls
 *    [[_setViewObject()]] and [[_renderView()]] for the view, then
 *    [[_setLayoutTemplates()]] and [[_renderLayout()]] for the layout.
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
abstract class Solar_Controller_Page extends Solar_Base {
    
    /**
     * 
     * The short-name for this controller, populated in _preRender().
     * 
     * @var string
     * 
     */
    public $controller;
    
    /**
     * 
     * The short-name for the executed action, populated in _preRender().
     * 
     * @var string
     * 
     */
    public $action;
    
    /**
     * 
     * The action being requested of (performed by) the page controller.
     * 
     * @var string
     * 
     */
    protected $_action = null;
    
    /**
     * 
     * The default page controller action.
     * 
     * @var string
     * 
     */
    protected $_action_default = null;
    
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
     * @todo Make the action key a little smarter.  Right now, you need to 
     * specify action names as "fooBar", not "actionFooBar" or "foo-bar".
     * Maybe a method "_getActionFormat()" to translate the key to the right
     * format (e.g., 'foo-bar' to "fooBar").
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
     * Request parameters collected from the URI pathinfo.
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
     * The short-name of this page controller.
     * 
     * @var string
     * 
     */
    protected $_controller = null;
    
    /**
     * 
     * Request parameters collected from the URI query string.
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
     * Default is 'process', as in $_POST['process'].
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
     * What is the default output format?
     * 
     * @var string
     * 
     */
    protected $_format_default = 'xhtml';
    
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
     * The response object with headers and body.
     * 
     * @var Solar_Http_Response
     * 
     */
    protected $_response;
    
    /**
     * 
     * The class used for view objects.
     * 
     * @var string
     * 
     */
    protected $_view_class = 'Solar_View';
    
    /**
     * 
     * The object used for rendering views and layouts.
     * 
     * @var Solar_View
     * 
     */
    protected $_view_object;
    
    /**
     * 
     * The front-controller object (if any) that invoked this page-controller.
     * 
     * @var Solar_Controller_Front
     * 
     */
    protected $_front;
    
    /**
     * 
     * Maps format name keys to Content-Type values.
     * 
     * When $this->_format matches one of the keys, the controller will set
     * the matching Content-Type header automatically in the response object.
     * 
     * @var array
     * 
     */
    protected $_format_type = array(
        'atom'      => 'application/atom+xml',
        'css'       => 'text/css',
        'htm'       => 'text/html',
        'html'      => 'text/html',
        'js'        => 'text/javascript',
        'json'      => 'application/json',
        'pdf'       => 'application/pdf',
        'ps'        => 'application/postscript',
        'rdf'       => 'application/rdf+xml',
        'rss'       => 'application/rss+xml',
        'rss2'      => 'application/rss+xml',
        'rtf'       => 'application/rtf',
        'text'      => 'text/plain',
        'txt'       => 'text/plain',
        'xml'       => 'application/xml',
    );
    
    /**
     * 
     * The character set to use when setting the Content-Type header.
     * 
     * @var string
     * 
     */
    protected $_charset = 'utf-8';
    
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
        
        // create the session object for this class
        $this->_session = Solar::factory(
            'Solar_Session',
            array('class' => $class)
        );
        
        // create the response object
        $this->_response = Solar::factory('Solar_Http_Response');
        
        // auto-set the name; for example Vendor_App_SomeThing => 'some-thing'
        if (empty($this->_controller)) {
            $pos = strrpos($class, '_');
            $this->_controller = substr($class, $pos + 1);
            $this->_controller = preg_replace('/([a-z])([A-Z])/', '$1-$2', $this->_controller);
            $this->_controller = strtolower($this->_controller);
        }
        
        // do parent construction
        parent::__construct($config);
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
        
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
     * Injects the front-controller object that invoked this page-controller.
     * 
     * @param Solar_Controller_Front $front The front-controller.
     * 
     * @return void
     * 
     */
    public function setFrontController($front)
    {
        $this->_front = $front;
    }
    
    /**
     * 
     * Executes the requested action and returns its output with layout.
     * 
     * If an exception is thrown during the fetch() process, it is caught
     * and sent along to the _exceptionDuringFetch() method, which may generate
     * and return alternative output.
     * 
     * @param string $spec The action specification string, for example,
     * "tags/php+framework" or "user/pmjones/php+framework?page=3"
     * 
     * @return Solar_Http_Response A response object with headers and body from
     * the action, view, and layout.
     * 
     */
    public function fetch($spec = null)
    {
        try {
            
            // load action, info, and query properties
            $this->_load($spec);
            
            // prerun hook
            $this->_preRun();
            
            // action chain, with pre- and post-action hooks
            $this->_forward($this->_action, $this->_info);
            
            // postrun hook
            $this->_postRun();
            
            // render the view and layout, with pre- and post-render hooks
            $this->_render();
            
            // set the Content-Type based on the format
            $this->_setContentType();
            
            // done, return the response headers, cookies, and body
            return $this->_response;
            
        } catch (Exception $e){
            
            // an exception was thrown somewhere, attempt to rescue it
            return $this->_exceptionDuringFetch($e);
            
        }
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
        $response = $this->fetch($spec);
        $response->display();
    }
    
    /**
     * 
     * Sets the response body based on the view, including layout, with
     * pre- and post-rendering logic.
     * 
     * @return void
     * 
     */
    protected function _render()
    {
        // if no view and no layout, there's nothing to render
        if (! $this->_view && ! $this->_layout) {
            return;
        }
        
        $this->_setViewObject();
        $this->_preRender();
        $this->_view_object->assign($this);
        
        if ($this->_view) {
            $this->_renderView();
        }
        
        if ($this->_layout) {
            $this->_setLayoutTemplates();
            $this->_renderLayout();
        }
        
        $this->_postRender();
    }
    
    /**
     * 
     * Sets $this->_view_object for rendering.
     * 
     * @return void
     * 
     */
    protected function _setViewObject()
    {
        // set up a view object, its template paths, and its helper stacks
        $this->_view_object = Solar::factory($this->_view_class);
        $this->_addViewTemplates();
        $this->_addViewHelpers();
    }
    
    /**
     * 
     * Uses $this->_view_object to render the view into $this->_response.
     * 
     * @return void
     * 
     */
    protected function _renderView()
    {
        // set the template name from the view and format
        $tpl = $this->_view
             . ($this->_format ? ".{$this->_format}" : "")
             . ".php";
        
        // fetch the view
        try {
            $this->_response->content = $this->_view_object->fetch($tpl);
        } catch (Solar_View_Exception_TemplateNotFound $e) {
            throw $this->_exception('ERR_VIEW_NOT_FOUND', array(
                'path' => $e->getInfo('path'),
                'name' => $e->getInfo('name'),
            ));
        }
    }
    
    /**
     * 
     * Uses $this->_view_object to render the layout into $this->_response.
     * 
     * @return void
     * 
     */
    protected function _renderLayout()
    {
        // assign the previous output
        $this->_view_object->assign($this->_layout_var, $this->_response->content);
        
        // set the template name from the layout value
        $tpl = $this->_layout . ".php";
        
        // fetch the layout
        try {
            $this->_response->content = $this->_view_object->fetch($tpl);
        } catch (Solar_View_Exception_TemplateNotFound $e) {
            throw $this->_exception('ERR_LAYOUT_NOT_FOUND', array(
                'path' => $e->getInfo('path'),
                'name' => $e->getInfo('name'),
            ));
        }
    }
    
    /**
     * 
     * Sets a Content-Type header in the response based on $this->_format.
     * 
     * @return void
     * 
     */
    protected function _setContentType()
    {
        // get the current format, or the default if not specified
        $format = $this->_format ? $this->_format : $this->_format_default;
        
        // do we have a content-type for the format?
        if (! empty($this->_format_type[$format])) {
            
            // yes, retain the content-type
            $val = $this->_format_type[$format];
            
            // add charset if one exists
            if ($this->_charset) {
                $val .= '; charset=' . $this->_charset;
            }
            
            // set the response header for content-type
            $this->_response->setHeader('Content-Type', $val);
        }
    }
    
    /**
     * 
     * Adds to the helper-class stack on a view object.
     * 
     * Automatically sets up a helper-class stack for you, searching
     * for helper classes in this order ...
     * 
     * 1. Vendor_App_Example_Helper_
     * 
     * 2. Vendor_App_Base_Helper_
     * 
     * 3. Vendor_View_Helper_
     * 
     * 4. Solar_View_Helper_
     * 
     * @return void
     * 
     */
    protected function _addViewHelpers()
    {
        // who is the vendor of this controller?
        $class = get_class($this);
        $pos = strpos($class, '_');
        $vendor = substr($class, 0, $pos);
        
        // if vendor is not Solar, add {Vendor}_View_Helper
        if ($vendor != 'Solar') {
            $this->_view_object->addHelperClass("{$vendor}_View_Helper");
        }
        
        // add custom helper classes
        $this->_view_object->addHelperClass($this->_helper_class);
        
        // get all parents including self
        $stack = Solar::parents(get_class($this), true);
        
        // remove the last two parents
        array_pop($stack); // Solar_Base
        array_pop($stack); // Solar_Controller_Page
        
        // add _Helper to each one
        foreach ($stack as $key => $val) {
            $stack[$key] = $val . '_Helper';
        }
        
        // add local helper classes
        $this->_view_object->addHelperClass($stack);
    }
    
    /**
     * 
     * Adds template paths to $this->_view_object.
     * 
     * The search-path will be in this order, for a Vendor_App_Example class
     * extended from Vender_App_Base ...
     * 
     * 1. Vendor/App/Example/View/
     * 
     * 2. Vendor/App/Base/View/
     * 
     * @return void
     * 
     */
    protected function _addViewTemplates()
    {
        // get the parents of the current class, including self
        $stack = Solar::parents(get_class($this), true);
        
        // remove Solar_Base and Solar_Controller_Page
        array_pop($stack);
        array_pop($stack);
        
        // convert underscores to slashes, and add /View
        foreach ($stack as $key => $val) {
            $stack[$key] = str_replace('_', '/', $val) . '/View';
        }
        
        // should we add Solar/App/Base/View for non-Solar vendors?
        
        // done, add the stack
        $this->_view_object->addTemplatePath($stack);
    }
    
    /**
     * 
     * Resets $this->_view_object to use the Layout templates.
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
     * @return void
     * 
     */
    protected function _setLayoutTemplates()
    {
        // get the parents of the current class, including self
        $stack = Solar::parents(get_class($this), true);
        
        // remove Solar_Base and Solar_Controller_Page
        array_pop($stack);
        array_pop($stack);
        
        // convert underscores to slashes, and add /Layout
        foreach ($stack as $key => $val) {
            $stack[$key] = str_replace('_', '/', $val) . '/Layout';
        }
        
        // should we add Solar/App/Base/Layout for non-Solar vendors?
        
        // done, add the stack
        $this->_view_object->setTemplatePath($stack);
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
            $this->_format = null;
        }
        
        // if the first param is the page name, drop it.
        // needed when no spec is passed and we're using the default URI.
        if (! empty($this->_info[0]) && $this->_info[0] == $this->_controller) {
            array_shift($this->_info);
        }
        
        // do we have an initial info element as an action method?
        if (empty($this->_info[0])) {
            // use the default action
            $this->_action = $this->_action_default;
        } else {
            // save it and remove from info
            $this->_action = array_shift($this->_info);
        }
        
        // are we asking for a non-default format?
        // the trim() lets us get a string-zero format.
        if (trim($this->_format) != '') {
            
            // what formats does the action allow?
            $action_format = $this->_getActionFormat($this->_action);
            
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
     * with Solar_Filter (or some other technique) before using it.
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
     * with Solar_Filter (or some other technique) before using it.
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
     * Redirects to another page and action, then calls exit(0).
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '302 Found'.
     * 
     * @return void
     * 
     */
    protected function _redirect($spec, $code = 302)
    {
        if ($spec instanceof Solar_Uri_Action) {
            $href = $spec->get(true);
        } elseif (strpos($spec, '://') !== false) {
            // external link, protect against header injections
            $href = str_replace(array("\r", "\n"), '', $spec);
        } else {
            $uri = Solar::factory('Solar_Uri_Action');
            $href = $uri->quick($spec, true);
        }
        
        // make sure there's actually an href
        $href = trim($href);
        if (! $href || trim($spec) == '') {
            throw $this->_exception('ERR_REDIRECT_FAILED', array(
                'spec' => $spec,
                'href' => $href,
            ));
        }
        
        // kill off all output buffers
        while(@ob_end_clean());
        
        // save the session
        session_write_close();
        
        // clear the response body
        $this->_response->content = null;
        
        // set headers and send the response directly
        $this->_response->setStatusCode($code);
        $this->_response->setHeader('Location', $href);
        $this->_response->display();
        exit(0);
    }
    
    /**
     * 
     * Redirects to another page and action after disabling HTTP caching.
     * 
     * The _redirect() method is often called after a successful POST
     * operation, to show a "success" or "edit" page. In such cases, clicking
     * clicking "back" or "reload" will generate a warning in the
     * browser allowing for a possible re-POST if the user clicks OK.
     * Typically this is not what you want.
     * 
     * In those cases, use _redirectNoCache() to turn off HTTP caching, so
     * that the re-POST warning does not occur.
     * 
     * This method sends the following headers before setting Location:
     * 
     * {{code: php
     *     header("Cache-Control: no-store, no-cache, must-revalidate");
     *     header("Cache-Control: post-check=0, pre-check=0", false);
     *     header("Pragma: no-cache");
     * }}
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '303 See Other'.
     * 
     * @return void
     * 
     */
    protected function _redirectNoCache($spec, $code = 303)
    {
        // reset cache-control
        $this->_response->setHeader(
            'Cache-Control',
            'no-store, no-cache, must-revalidate'
        );
        
        // append cache-control
        $this->_response->setHeader(
            'Cache-Control',
            'post-check=0, pre-check=0',
            false
        );
        
        // reset pragma header
        $this->_response->setHeader('Pragma', 'no-cache');
        
        // continue with redirection
        return $this->_redirect($spec, $code);
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
        
        // run this before every action, may change the requested action.
        $this->_preAction();
        
        // does a related action-method exist?
        $method = $this->_getActionMethod($this->_action);
        if (! $method) {
            
            // no method found for the action.
            // this is the last thing we do in this chain.
            $this->_notFound($this->_action, $params);
            
        } else {
            
            // set the view to the requested action
            $this->_view = $this->_getActionView($this->_action);
        
            // run the action method, which may itself _forward() to other
            // actions.  pass all parameters in order.
            call_user_func_array(
                array($this, $method),
                $params
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
     * 'preview', etc.  If empty, returns true if *any* process type
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
        
        // didn't ask for a process type; answer if *any* process was
        // requested.
        if (empty($type)) {
            $any = $this->_request->post($process_key);
            return ! empty($any);
        }
        
        // asked for a process type, find the locale string for it.
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
        $method = str_replace('-', ' ', $action);
        $method = ucwords(trim($method));
        $method = 'action' . str_replace(' ', '', $method);
        
        // does the method exist?
        if (method_exists($this, $method)) {
            return $method;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns the allowed format list for a given action.
     * 
     * Allows the use of "foo-bar" (preferred), "fooBar", or "actionFooBar"
     * as the action key in the action_format array.
     * 
     * @param string $action The action name.
     * 
     * @return array The list of formats allowed for the action.
     * 
     */
    protected function _getActionFormat($action)
    {
        // skip if there are no action formats
        if (empty($this->_action_format)) {
            return array();
        }
        
        // look for the action as passed (foo-bar) in action_format
        $key = $action;
        if (! empty($this->_action_format[$key])) {
            return (array) $this->_action_format[$key];
        }
        
        // convert the action to method style (foo-bar to fooBar) and look again
        $key = str_replace('-', ' ', $action);
        $key = ucwords(trim($key));
        $key = str_replace(' ', '', $key);
        $key[0] = strtolower($key[0]);
        if (! empty($this->_action_format[$key])) {
            return (array) $this->_action_format[$key];
        }
        
        // convert the action to full method style (actionFooBar) and look for
        // the last time
        $key = 'action' . ucfirst($key);
        if (! empty($this->_action_format[$key])) {
            return (array) $this->_action_format[$key];
        }
        
        // fail
        return array();
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
        $view = str_replace('-', ' ', $action);
        $view = ucwords(trim($view));
        $view = str_replace(' ', '', $view);
        $view[0] = strtolower($view[0]);
        return $view;
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
     * Use this to pre-process $this->_view_object, or to manipulate
     * controller properties with view helpers.
     * 
     * The default implementation sets the locale class for the getText
     * helper.
     * 
     * @return void
     * 
     */
    protected function _preRender()
    {
        // set the locale class for the getText helper
        $class = get_class($this);
        $this->_view_object->getHelper('getTextRaw')->setClass($class);
        
        // set the public controller and action vars
        $this->controller = $this->_controller;
        $this->action     = $this->_action;
    }
    
    /**
     * 
     * Executes after rendering the page view and layout.
     * 
     * Use this to do a final filter or maniuplation of $this->_response
     * from the view and layout scripts.  By default, it leaves the
     * response alone.
     * 
     * @return void
     * 
     */
    protected function _postRender()
    {
    }
    
    /**
     * 
     * Executes when _forward() cannot find a method for the requested action.
     * 
     * This default implementation throws an exception, but extended classes
     * may override the behavior to be the action that executes when the
     * requested action was not found.
     * 
     * @param string $action The name for the action that was not found.
     * 
     * @param string $params The params for the action that was not found.
     * 
     * @return void
     * 
     */
    protected function _notFound($action, $params)
    {
        throw $this->_exception(
            'ERR_ACTION_NOT_FOUND',
            array(
                'action' => $action,
                'params' => $params,
            )
        );
    }
    
    /**
     * 
     * When an exception is thrown during the fetch() process, use this
     * method to recover from it.
     * 
     * This default implementation just re-throws the exception, but extended
     * classes may override the behavior to return alternative output from
     * the failed fetch().
     * 
     * @param Exception $e The exception thrown during the fetch() process.
     * 
     * @return string The alternative output from the rescued exception.
     * 
     */
    protected function _exceptionDuringFetch(Exception $e)
    {
        throw $this->_exception('ERR_DURING_FETCH', array(
            'exception' => $e,
        ));
    }
}
