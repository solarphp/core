<?php
/**
 * 
 * Factory class for session manager.
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Handler.php 3850 2009-06-24 20:18:27Z pmjones $
 * 
 */
class Solar_Session_Manager extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The class to factory, for example
     *   'Solar_Session_Manager_Adapter_Native'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Session_Manager = array(
        'adapter' => 'Solar_Session_Manager_Adapter_Native',
    );
}
