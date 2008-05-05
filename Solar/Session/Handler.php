<?php
/**
 * 
 * Factory class for session save-handlers.
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Session_Handler extends Solar_Base
{
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The class to factory, for example
     *   'Solar_Session_Handler_Adapter_Native'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Session_Handler = array(
        'adapter' => 'Solar_Session_Handler_Adapter_Native',
    );
    
    /**
     * 
     * Factory method to create session adapter objects.
     * 
     * @return Solar_Session_Handler_Adapter
     * 
     */
    public function solarFactory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        // return the factoried adapter object
        return Solar::factory($class, $config);
    }
}
