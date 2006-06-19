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
    
    /**
     * 
     * Overrides the general Solar_App setup so that we don't need a
     * database connection. This is because we want the simplest
     * possible hello-world example.
     * 
     * Thanks, Clay Loveless, for suggesting this.
     * 
     */
    protected function _setup()
    {
        // register a Solar_User object if not already.
        // this will trigger the authentication process.
        if (! Solar::isRegistered('user')) {
            Solar::register('user', Solar::factory('Solar_User'));
        }
        
        // set the layout title
        $this->layout_title = get_class($this);
    }
}
?>