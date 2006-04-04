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
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
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
     * Sets up the Solar_App environment.
     * 
     * Registers 'sql', 'user', and 'content' objects, and sets the
     * layout title to the class name.
     * 
     * @return void
     * 
     */
    public function _setup()
    {
        // register a Solar_Sql object if not already
        if (! Solar::inRegistry('sql')) {
            Solar::register('sql', Solar::factory('Solar_Sql'));
        }
        
        // register a Solar_User object if not already.
        // this will trigger the authentication process.
        if (! Solar::inRegistry('user')) {
            Solar::register('user', Solar::factory('Solar_User'));
        }
        
        // register a Solar_Content object if not already.
        if (! Solar::inRegistry('content')) {
            Solar::register('content', Solar::factory('Solar_Content'));
        }
        
        // set the layout title
        $this->layout_title = get_class($this);
    }
}
?>