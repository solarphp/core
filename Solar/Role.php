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

/**
 * 
 * Class for reading user roles and groups.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 */
class Solar_Role extends Solar_Base {
    
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
     */
    public function solarFactory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        // deprecated: support a 'config' key for the adapter configs.
        // this was needed for facades, but is not needed for factories.
        if (isset($config['config'])) {
            $tmp = $config['config'];
            unset($config['config']);
            $config = array_merge($config, (array) $tmp);
        }
        
        return Solar::factory($class, $config);
    }
}
