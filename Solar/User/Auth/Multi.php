<?php
/**
 * 
 * Authenticate against multiple sources, falling back as needed.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
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
 * @package Solar_User
 * 
 */
class Solar_User_Auth_Multi extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are:
     * 
     * drivers => (array) The array of driver classes and optional configs.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'drivers' => array(
            'Solar_User_Auth_None'
        )
    );
    
    /**
     * 
     * An array of the multiple driver instances.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_driver = array();
    
    /**
     * 
     * Constructor.
     * 
     */
    public function __construct($config = null)
    {
        // basic construction
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
     * Validate a username and password.
     *
     * @param string $user Username to authenticate.
     * 
     * @param string $pass The plain-text password to use.
     * 
     * @return boolean|Solar_Error True on success, false on failure,
     * or a Solar_Error object if there was a driver error.
     * 
     */
    public function valid($user, $pass)
    {
        foreach ($this->_driver as $driver) {
            if ($driver->valid($user, $pass)) {
                return true;
            }
        }
        return false;
    }
}
?>