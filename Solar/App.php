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
 * @license LGPL
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
    
    public $layout_title = __CLASS__;
    
    public $layout_base = null;
    
    public $layout_meta = array();
    
    public $layout_link = array();
    
    public $layout_style = array();
    
    public $layout_script = array();
    
    public $layout_object = array();
    
    public $layout_top = array();
    
    public $layout_left = array();
    
    public $layout_right = array();
    
    public $layout_bottom = array();
    
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
    }
}
?>