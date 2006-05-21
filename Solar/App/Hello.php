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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Application controller class.
 */
Solar::loadClass('Solar_App');

/**
 * 
 * Simple "hello world" application with actions, views, and localization.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_Hello
 * 
 */
class Solar_App_Hello extends Solar_App {
    
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