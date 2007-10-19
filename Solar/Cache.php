<?php
/**
 * 
 * Factory class for cache adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
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
 * Factory class for cache adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 */
class Solar_Cache extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The adapter class for the factory, default 
     * 'Solar_Cache_Adapter_File'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache = array(
        'adapter' => 'Solar_Cache_Adapter_File',
    );
    
    /**
     * 
     * Factory method to create cache adapter objects.
     * 
     * @return Solar_Cache_Adapter
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
