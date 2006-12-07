<?php
/**
 * 
 * Factory class for reading access privileges.
 * 
 * @category Solar
 * 
 * @package Solar_Access
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
 * Factory class for reading access privileges.
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 */
class Solar_Access extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * @var array
     */
    protected $_Solar_Access = array(
        'adapter' => 'Solar_Access_Adapter_Open',
    );
    
    /**
     * 
     * Factory method for returning adapters.
     * 
     */
    public function factory()
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
