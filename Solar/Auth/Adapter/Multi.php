<?php
/**
 * 
 * Authenticate against multiple sources, falling back as needed.
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
 * Authenticate against multiple sources, falling back as needed.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth_Adapter_Multi extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are:
     * 
     * : \\adapters\\ : (array) The array of adapter classes and optional configs.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'adapters' => array(
            'Solar_Auth_None'
        )
    );
    
    /**
     * 
     * An array of the multiple adapter instances.
     * 
     * @var array
     * 
     */
    protected $_adapter = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-supplied configuration.
     * 
     */
    public function __construct($config = null)
    {
        // basic construction
        parent::__construct($config);
        
        // make sure the adapters config is an array
        settype($this->_config['adapters'], 'array');
        
        // instantiate the adapter objects
        foreach ($this->_config['adapters'] as $key => $info) {
            
            // is the adapter value an array (for custom configs)
            // or a string (for default configs)?
            if (is_array($info)) {
                $class = $info[0];
                $opts = $info[1];
            } else {
                $class = $info;
                $opts = null;
            }
            
            // add the adapter instance
            $this->_adapter[] = Solar::factory($class, $opts);
        }
    }
    
    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    protected function _verify()
    {
        $handle = $this->_handle;
        $passwd = $this->_passwd;
        
        foreach ($this->_adapter as $adapter) {
            if ($adapter->isValid($handle, $passwd)) {
                return true;
            }
        }
        return false;
    }
}
?>