<?php
/**
 * 
 * Abstract role adapter.
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
 * @todo rename to Unix, add Ini file handler as well
 * 
 */

/**
 * 
 * Abstract role adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 */
abstract class Solar_Role_Adapter extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `refresh`
     * : (bool) Whether or not to refresh (reload) roles every time load() is
     *   called.  The default is to load into the session once, then not load
     *   again even though load() gets called.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role_Adapter = array(
        'refresh' => false,
    );
    
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
     * A class-segmented session-variable reference.
     * 
     * @var Solar_Session
     * 
     */
    protected $_session;
    
    /**
     * 
     * A public reference to the session store.
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
        
        // get a session segment
        $this->_session = Solar::factory(
            'Solar_Session',
            array('class' => get_class($this))
        );
        
        // make sure we have a session value and reference to it.
        $this->list =& $this->_session->store;
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
    public function load($handle, $refresh = null)
    {
        if (is_null($refresh)) {
            $refresh = $this->_config['refresh'];
        }
        
        // have we loaded roles for the first time yet? if so, and if
        // we're not forcing refreshes, the we don't need to do
        // anything, just return the list as it is right now.
        if ($this->_loaded && ! $refresh) {
            return $this->list;
        }
        
        // reset the roles list
        $this->reset();
        
        // fetch the role list using the adapter-specific method
        $result = $this->fetch($handle);
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
    
    /**
     * 
     * Adapter-specific method to find roles for loading.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    abstract public function fetch($handle);
}
