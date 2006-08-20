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
     * Keys are:
     * 
     * `adapter`:
     * (string) The adapter class to use.
     * 
     * `config`:
     * (array) Config options for constructing the adapter class.
     * 
     * `refresh`:
     * (bool) Whether or not to refresh the roles on every load.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role = array(
        'adapter' => 'Solar_Role_Adapter_None',
        'config'  => null,
        'refresh' => false,
    );
    
    /**
     * 
     * The adapter instance.
     * 
     * @var array
     * 
     */
    protected $_adapter = array();
    
    /**
     * 
     * Have we attempted to load the list of roles yet?
     * 
     * @var bool
     * 
     */
    protected $_loaded = false;
    
    /**
     * 
     * A convenient reference to $_SESSION['Solar_Role'].
     * 
     * @var array
     * 
     */
    public $list;
    
    /**
     * 
     * Constructor to set up the storage adapter.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic config option settings
        parent::__construct($config);
        
        // instantiate a adapter object
        $this->_adapter = Solar::factory(
            $this->_config['adapter'],
            $this->_config['config']
        );
        
        // make sure we have a session value and reference to it.
        if (! isset($this->list)) {
            $_SESSION['Solar_Role'] = array();
            $this->list =& $_SESSION['Solar_Role'];
        }
    }
    
    /**
     * 
     * Load the list of roles for the given user from the adapter.
     * 
     * @param string $handle The username to load roles for.
     * 
     * @return void
     * 
     */
    public function load($handle)
    {
        // have we loaded roles for the first time yet? if so, and if
        // we're not forcing refreshes, the we don't need to do
        // anything, just return the list as it is right now.
        if ($this->_loaded && ! $this->_config['refresh']) {
            return $this->list;
        }
        
        // reset the roles list
        $this->reset();
        
        // fetch the role list
        $result = $this->_adapter->fetch($handle);
        if ($result) {
            // merge the results into the common list
            $this->list = array_merge(
                $this->list,
                (array) $result
            );
        }
        
        // OK, we've loaded what we can.
        $this->_loaded = true;
    }
    
    /**
     * 
     * Resets the role list to nothing.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->_loaded = false;
        $this->list = array();
    }
    
    /**
     * 
     * Check to see if a user is in a role.
     * 
     * @param string $role The role to check.
     * 
     * @return bool True if the user is in the role, or false if not.
     * 
     */
    public function is($role = null)
    {
        return in_array($role, $this->list);
    }
    
    /**
     * 
     * Check to see if a user is in any of the listed roles.
     * 
     * @param string|array $roles The role(s) to check.
     * 
     * @return bool True if the user is in any of the listed roles (a
     * logical 'or'), false if not.
     * 
     */
    public function isAny($roles = array())
    {
        // loop through all of the roles, returning 'true' the first
        // time we find a matching role.
        foreach ((array) $roles as $role) {
            if (in_array($role, $this->list)) {
                return true;
            }
        }
        
        // we got through the whole array without finding a match.
        // therefore, user was not in any of the roles.
        return false;
    }
    
    /**
     * 
     * Check to see if a user is in all of the listed roles.
     * 
     * @param string|array $roles The role(s) to check.
     * 
     * @return bool True if the user is in all of the listed roles (a
     * logical 'and'), false if not.
     * 
     */
    public function isAll($roles = array())
    {
        // loop through all of the roles, returning 'false' the first
        // time we find the user is not in one of the roles.
        foreach ((array) $roles as $role) {
            if (! in_array($role, $this->list)) {
                return false;
            }
        }
        
        // we got through the whole list; therefore, the user is in all
        // of the noted roles.
        return true;
    }
}
?>