<?php
/**
 * 
 * Simple "hello world" application with actions, views, and localization.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Application controller class.
 */
Solar::loadClass('Solar_Controller_Page');

/**
 * 
 * Simple "hello world" application with actions, views, and localization.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 */
class Solar_App_HelloWorld extends Solar_Controller_Page {
    
    /**
     * 
     * The default controller action.
     * 
     * @var string
     * 
     */
    protected $_action_default = 'main';
    
    /**
     * 
     * The list of available locale codes.
     * 
     * @var array
     * 
     */
    public $list = array('en_US', 'es_ES', 'fr_FR');
    
    /**
     * 
     * The requested locale code.
     * 
     * @var string
     * 
     */
    public $code;
    
    
    /**
     * 
     * The translated text.
     * 
     * @var string
     * 
     */
    public $text;
}
?>