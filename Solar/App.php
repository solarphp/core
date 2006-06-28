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

/**
 * Page-controller class.
 */
Solar::loadClass('Solar_Controller_Page');

/**
 * 
 * Abstract base class for Solar application classes.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 */
abstract class Solar_App extends Solar_Controller_Page {
    
    /**
     * 
     * Default layout to be rendered.
     * 
     * @var string
     * 
     */
    protected $_layout = 'twoColRight';
    
    /**
     * 
     * The <title> tag value for the layout <head> block.
     * 
     * @var string
     * 
     */
    public $layout_title = __CLASS__;
    
    /**
     * 
     * The <base> href value for the layout <head> block.
     * 
     * @var string
     * 
     */
    public $layout_base = null;
    
    /**
     * 
     * An array of <meta> tag values for the layout <head> block.
     * 
     * @var array
     * 
     */
    public $layout_meta = array();
    
    /**
     * 
     * An array of <link> tag values for the layout <head> block.
     * 
     * @var array
     * 
     */
    public $layout_link = array();
    
    /**
     * 
     * An array of <style> tag values for the layout <head> block.
     * 
     * @var array
     * 
     */
    public $layout_style = array('Solar/styles/default.css');
    
    /**
     * 
     * An array of <script> tag values for the layout <head> block.
     * 
     * @var array
     * 
     */
    public $layout_script = array();
    
    /**
     * 
     * An array of <object> tag values for the layout <head> block.
     * 
     * @var array
     * 
     */
    public $layout_object = array();
    
    /**
     * 
     * An array of content for the layout <div id="top"> block.
     * 
     * @var array
     * 
     */
    public $layout_top = array();
    
    /**
     * 
     * An array of content for the layout <div id="left"> block.
     * 
     * @var array
     * 
     */
    public $layout_left = array();
    
    /**
     * 
     * An array of content for the layout <div id="right"> block.
     * 
     * @var array
     * 
     */
    public $layout_right = array();
    
    /**
     * 
     * An array of content for the layout <div id="bottom"> block.
     * 
     * @var array
     * 
     */
    public $layout_bottom = array();
    
    /**
     * 
     * Error messages, usually for the 'error' action/view.
     * 
     * @var array
     * 
     */
    public $errors;
    
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
        if (! Solar::isRegistered('sql')) {
            Solar::register('sql', Solar::factory('Solar_Sql'));
        }
        
        // register a Solar_Content object if not already.
        if (! Solar::isRegistered('content')) {
            Solar::register('content', Solar::factory('Solar_Content'));
        }
        
        // register a Solar_User object if not already.
        // this will trigger the authentication process.
        if (! Solar::isRegistered('user')) {
            Solar::register('user', Solar::factory('Solar_User'));
        }
        
        // set the layout title
        $this->layout_title = get_class($this);
    }
    
    /**
     * 
     * Checks to see if user is allowed access.
     * 
     * On access failure, changes $this->_action to 'error' and adds
     * an error message stating the user is not allowed access.
     * 
     * @return void
     * 
     */
    protected function _preAction()
    {
        // generic security check
        $class = get_class($this);
        $action = $this->_action;
        $allow = Solar::registry('user')->access->isAllowed($class, $action);
        if (! $allow) {
            $this->errors[] = $this->locale('ERR_NOT_ALLOWED_ACCESS');
            $this->_action = 'error';
        }
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
}
?>