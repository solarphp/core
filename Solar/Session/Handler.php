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
class Solar_Session_Handler extends Solar_Factory
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
}
