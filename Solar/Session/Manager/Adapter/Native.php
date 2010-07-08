<?php
/**
 * 
 * Session manager for native PHP sessions
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Native.php 3366 2008-08-26 01:36:49Z pmjones $
 * 
 */
class Solar_Session_Manager_Adapter_Native extends Solar_Session_Manager_Adapter
{

    /**
     * 
     * Default configuration values.
     * 
     * @config dependency handler A Solar_Session_Handler dependency injection. Default
     *   is the string 'php', which means to use the native PHP session save.
     *   handler instead of a dependency injection.
     * 
     * @var array
     * 
     */
    protected $_Solar_Session_Manager_Adapter_Native = array(
        'handler' => null,
    );

    /**
     * 
     * The session save handler object.
     * 
     * @var Solar_Session_Handler_Adapter
     * 
     */
    static protected $_handler;

    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // only set up the handler if it doesn't exist yet.
        if (! self::$_handler) {
            self::$_handler = Solar::dependency(
                'Solar_Session_Handler',
                $this->_config['handler']
            );
        }
    }

}