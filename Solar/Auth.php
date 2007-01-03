<?php
/**
 * 
 * Class for checking user authentication credentials.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
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
 * Class for checking user authentication credentials.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The adapter class, for example 'Solar_Auth_Adapter_File'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth = array(
        'adapter' => 'Solar_Auth_Adapter_None',
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
