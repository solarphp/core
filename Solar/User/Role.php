<?php

/**
 * 
 * Class for reading user roles and groups.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User
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
 * Class for reading user roles and groups.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User
 * 
 */

class Solar_User_Role extends Solar_Base {
    
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are:
     * 
     * refresh => (bool) Whether or not to refresh the groups on every load.
     * 
     * class => (string) The driver class to use.
     * 
     * options => (array) Config options for constructing the driver class.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    
    protected $_config = array(
        'class'   => 'Solar_User_Role_None',
        'options' => null,
        'refresh' => false,
    );
    
    
    /**
     * 
     * The driver instance.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    
    protected $_driver = array();
    
    
    /**
     * 
     * Have we attempted to load the list of roles yet?
     * 
     * @access protected
     * 
     * @var bool
     * 
     */
    
    protected $_loaded = false;
    
    
    /**
     * 
     * A convenient reference to $_SESSION['Solar_User_Role'].
     * 
     * @access public
     * 
     * @var array
     * 
     */
    
    public $list;
    
    
    /**
     * 
     * Constructor to set up the storage driver.
     *
     * @access public
     * 
     * @param array $config The config options.
     * 
     * @return object
     * 
     */
    
    public function __construct($config = null)
    {
        // basic config option settings
        parent::__construct($config);
        
        // instantiate a driver object
        $this->_driver = Solar::object(
            $this->_config['class'],
            $this->_config['options']
        );
        
        // make sure we have a session value and reference to it.
        if (! isset($this->list)) {
            $_SESSION['Solar_User_Role'] = array();
            $this->list =& $_SESSION['Solar_User_Role'];
        }
    }
    
    
    /**
     * 
     * Refresh the list of roles for the given user.
     * 
     * @access public
     * 
     * @return array The list of roles for the authenticated user.
     * 
     */
    
    public function fetch($username)
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
        $result = $this->_driver->fetch($username);
            
        // let errors go silently from here
        if (! Solar::isError($result) && $result !== false) {
            // merge the results into the common list
            $this->list = array_merge(
                $this->list,
                (array) $result
            );
        }
        
        // OK, we've loaded what we can.
        $this->_loaded = true;
        
        // return the results
        return $this->list;
    }
    
    
    /**
     * 
     * Resets the role list to nothing.
     * 
     * @access public
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
     * @access public
     * 
     * @param string $role The role to check.
     * 
     * @return void
     * 
     */
    
    public function in($role = null)
    {
        return in_array($role, $this->list);
    }
    
    
    /**
     * 
     * Check to see if a user is in any of the listed roles.
     * 
     * @access public
     * 
     * @param string|array $roles The role(s) to check.
     * 
     * @return boolean True if the user is in any of the listed roles (a
     * logical 'or'), false if not.
     * 
     */
    
    public function inAny($roles = array())
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
     * @access public
     * 
     * @param string|array $roles The role(s) to check.
     * 
     * @return boolean True if the user is in all of the listed roles (a
     * logical 'and'), false if not.
     * 
     */
    
    public function inAll($roles = array())
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