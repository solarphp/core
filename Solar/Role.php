<?php
/**
 * 
 * Class for reading user roles and groups.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Role extends Solar_Base
{
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The adapter class to use.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role = array(
        'adapter' => 'Solar_Role_Adapter_None',
    );
    
    /**
     * 
     * Factory method for returning adapters.
     * 
     * @return Solar_Role_Adapter
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
