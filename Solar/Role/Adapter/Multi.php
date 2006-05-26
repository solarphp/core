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
     * : \\drivers\\ : (array) The array of driver classes and optional configs.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'drivers' => array(
            'Solar_User_Role_None'
        )
    );
    
    /**
     * 
     * An array of the multiple driver instances.
     * 
     * @var array
     * 
     */
    protected $_driver = array();
    
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
        
        // make sure the drivers config is an array
        settype($this->_config['drivers'], 'array');
        
        // instantiate the driver objects
        foreach ($this->_config['drivers'] as $key => $info) {
            
            // is the driver value an array (for custom configs)
            // or a string (for default configs)?
            if (is_array($info)) {
                $class = $info[0];
                $opts = $info[1];
            } else {
                $class = $info;
                $opts = null;
            }
            
            // add the driver instance
            $this->_driver[] = Solar::factory($class, $opts);
        }
    }
    
    /**
     * 
     * Fetches the roles from each of the drivers.
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
        
        // loop through all the drivers and collect roles
        foreach ($this->_driver as $obj) {
        
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