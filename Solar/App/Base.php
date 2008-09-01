<?php
/**
 * 
 * Abstract base class for Solar application classes.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
abstract class Solar_App_Base extends Solar_Controller_Page {
    
    /**
     * 
     * The name of the layout being used; populated from $this->_layout.
     * 
     * @var string
     * 
     */
    public $layout;
    
    /**
     * 
     * Error messages, usually for the 'error' action/view.
     * 
     * In some cases, this may be an Exception object.
     * 
     * @var array|Exception
     * 
     */
    public $errors;
    
    /**
     * 
     * Values for the <head> block in the layout.
     * 
     * Keys are:
     * 
     * `title`
     * : (string) The <title> tag value.
     * 
     * `base`
     * : (string) The <base> href value.
     * 
     * `meta`
     * : (array) An array of <meta> tag values.
     * 
     * `link`
     * : (array) An array of <link> tag values.
     * 
     * `style`
     * : (array) An array of <style> tag values.
     * 
     * `script`
     * : (array) An array of <script> tag values.
     * 
     * `object`
     * : (array) An array of <object> tag values.
     * 
     * @var array
     * 
     */
    public $layout_head = array(
        'title'  => null,
        'base'   => null,
        'meta'   => array(),
        'link'   => array(),
        'style'  => array(),
        'script' => array(),
        'object' => array(),
    );
    
    /**
     * 
     * Local navigation links.
     * 
     * Format is "link href" => "display text".
     * 
     * @var array
     * 
     */
    public $layout_local = array();
    
    /**
     * 
     * The currently-active local navigation link.
     * 
     * Refers to a key in [[$layout_local]].
     * 
     * @var array
     * 
     */
    public $layout_local_active = null;
    
    /**
     * 
     * Site navigation links.
     * 
     * Format is "link href" => "display text".
     * 
     * @var array
     * 
     */
    public $layout_nav = array();
    
    /**
     * 
     * The currently-active site navigation link.
     * 
     * Refers to a key in [[$layout_nav]].
     * 
     * @var array
     * 
     */
    public $layout_nav_active = null;
    
    /**
     * 
     * Name of the layout to be rendered.
     * 
     * Default is "navtop-localright".
     * 
     * @var string
     * 
     */
    protected $_layout_default = 'navtop-localright';
    
    /**
     * 
     * Sets up the Solar_App environment.
     * 
     * Registers 'sql', 'user', and 'content' objects, and sets the
     * layout title to the class name.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // register a Solar_Sql object if not already
        if (! Solar_Registry::exists('sql')) {
            Solar_Registry::set('sql', Solar::factory('Solar_Sql'));
        }
        
        // register a Solar_User object if not already.
        // this will trigger the authentication process.
        if (! Solar_Registry::exists('user')) {
            Solar_Registry::set('user', Solar::factory('Solar_User'));
        }
        
        // set the layout title
        $this->layout_head['title'] = get_class($this);
    }
    
    /**
     * 
     * Checks to see if user is allowed access to the requested action
     * for this controller.
     * 
     * On access failure, changes $this->_action to 'error' and adds
     * an error message stating the user is not allowed access.
     * 
     * @return void
     * 
     */
    protected function _preAction()
    {
        $allow = Solar_Registry::get('user')->access->isAllowed(
            get_class($this),
            $this->_action
        );
        
        if (! $allow) {
            $this->errors[] = $this->locale('ERR_NOT_ALLOWED_ACCESS');
            $this->_action = 'error';
        }
    }
    
    /**
     * 
     * Calls parent _preRender(), then sets additional view properties.
     * 
     * @return void
     * 
     */
    protected function _preRender()
    {
        parent::_preRender();
        
        // let the view know what layout this is
        $this->layout = $this->_layout;
        
        // add an app-specific CSS file
        $tmp = explode('_', get_class($this));
        $vendor = $tmp[0];
        $this->layout_head['style'][] = "{$vendor}/styles/app/{$this->_controller}.css";
    }
    
    /**
     * 
     * Shows a generic error page.
     * 
     * @return void
     * 
     */
    public function actionError()
    {
        // no code needed, just dumps $this->errors via the 'error.php'
        // view
    }
    
    /**
     * 
     * Use this to set the action to "error" with a locale key to be
     * translated.
     * 
     * @param string $locale_key The error-message locale key.
     * 
     * @return void
     * 
     */
    protected function _error($locale_key)
    {
        $this->errors[] = $this->locale($locale_key);
        return $this->_forward('error');
    }
    
    /**
     * 
     * Shows the "error" page.
     * 
     * @param string $action The name of the not-found action.
     * 
     * @param array $params The params for the not-found action.
     * 
     * @return void
     * 
     */
    protected function _notFound($action, $params)
    {
        $this->_response->setStatusCode(404);
        $this->errors[] = $this->locale('ACTION_NOT_FOUND');
        $this->errors[] = "Action: $action";
        foreach ($params as $key => $val) {
            $this->errors[] = "Param $key: $val";
        }
        return $this->_forward('error');
    }
    
    /**
     * 
     * Shows the "exception during fetch" page.
     * 
     * @param Exception $e The exception encountered during fetch().
     * 
     * @return Solar_Response A response object with a 500 status code and
     * a page describing the exception.
     * 
     */
    protected function _exceptionDuringFetch(Exception $e)
    {
        $this->errors[] = $e;
        $this->_layout = null;
        $this->_view = 'exception';
        $this->_format = null;
        $this->_response->setStatusCode(500);
        
        $this->_render();
        return $this->_response;
    }
}
