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
    
}
?>