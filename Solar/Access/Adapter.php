<?php
/**
 * 
 * Abstract adapter for reading access privileges.
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
abstract class Solar_Access_Adapter extends Solar_Base {
    
    /**
     * 
     * The access list for a handle and roles.
     * 
     * @var array
     * 
     */
    public $list = array();
    
    /**
     * 
     * A Solar_Auth object representing the current user.
     * 
     * @var Solar_Auth_Adapter
     * 
     */
    protected $_auth;
    
    /**
     * 
     * A Solar_Role object representing the current user.
     * 
     * @var Solar_Role_Adapter
     * 
     */
    protected $_role;
    
    /**
     * 
     * Fetches the access list from the adapter into $this->list.
     * 
     * @param string|Solar_Auth_Adapter $auth_spec Fetch access controls for
     * this user handle.  If a string, is assumed to be the handle directly;
     * otherwise, the handle is pulled from a Solar_Auth_Adapter object.
     * 
     * @param array|Solar_Auth_Adapter $auth_spec Fetch access controls for
     * these user roles.  If an array, is assumed to be the roles directly;
     * otherwise, the roles are pulled from a Solar_Role_Adapter object.
     * 
     * @return void
     * 
     */
    public function load($auth_spec, $role_spec)
    {
        // clear out previous values
        $this->reset();
        
        if ($auth_spec instanceof Solar_Auth_Adapter) {
            $this->_auth = $auth_spec;
            $handle = $this->_auth->handle;
        } else {
            $handle = $auth_spec;
        }
        
        if ($role_spec instanceof Solar_Role_Adapter) {
            $this->_role = $role_spec;
            $roles = $this->_role->list;
        } else {
            $roles = $role_spec;
        }
        
        // get the access list
        $list = $this->fetch($handle, $roles);
        
        // reverse so that last ones are checked first
        $this->list = array_reverse($list);
    }
    
    /**
     * 
     * Tells whether or not to allow access to a class/action/process combination.
     * 
     * @param string $class The name of the class to control; use '*' for
     * all values.
     * 
     * @param string $action The action within that class; use '*' for
     * all values.  For handle types, use '+' to indicate any non-empty
     * handle (i.e., any authenticated user).
     * 
     * @param mixed $content A content item (application-specific) to check
     * ownership on.
     * 
     * @return bool True if the current handle or role is allowed 
     * access, false if not.
     * 
     * @see isOwner()
     * 
     */
    public function isAllowed($class = '*', $action = '*', $content = null)
    {
        foreach ($this->list as $info) {
            $class_match   = ($info['class']   == $class   || $info['class']  == '*');
            $action_match  = ($info['action']  == $action  || $info['action'] == '*');
            if ($class_match && $action_match) {
                // do we also need to be the owner?
                if ($info['type'] == 'owner' && ! $this->isOwner($content)) {
                    // not the owner, skip to the next control item
                    continue;
                }
                
                // class and action matched (and optionally owner).
                // return the flag.
                return (bool) $info['allow'];
            }
        }
        
        // no matching params, deny by default
        return false;
    }
    
    /**
     * 
     * Resets the current access controls to a blank array, along with the 
     * $_auth and $_role properties.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->_auth = null;
        $this->_role = null;
        $this->list = array();
    }
    
    /**
     * 
     * Fetch access privileges for a user handle and roles.
     * 
     * @param string $handle The user handle.
     * 
     * @param array $roles The user roles.
     * 
     * @return array
     * 
     */
    abstract public function fetch($handle, $roles);
    
    /**
     * 
     * Checks to see if the current user is the owner of application-specific
     * content.
     * 
     * @param mixed $content The content to check ownership of.
     * 
     * @return bool
     * 
     */
    abstract public function isOwner($content);
}
