<?php
/**
 * 
 * Adapter to fetch roles from multiple sources and return as a single list.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Abstract role adapter class.
 */
Solar::loadClass('Solar_Role_Adapter');

/**
 * 
 * Adapter to fetch roles from multiple sources and return as a single list.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_Role_Adapter_Multi extends Solar_Role_Adapter {
    
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
            'Solar_User_Role_None'
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
     * @param array $config User defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic config
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
     * Fetches the roles from each of the adapters.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return mixed An array of discovered roles.
     * 
     */
    public function fetch($handle)
    {
        // the list of all roles
        $list = array();
        
        // loop through all the adapters and collect roles
        foreach ($this->_adapter as $obj) {
        
            // fetch the role list
            $result = $obj->fetch($handle);
            if ($result) {
                // merge the results into the common list
                $list = array_merge(
                    $list,
                    (array) $result
                );
            }
        }
        
        return $list;
    }
}
?>