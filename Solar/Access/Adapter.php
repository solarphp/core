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

/**
 * 
 * Abstract adapter for reading access privileges.
 * 
 * @category Solar
 * 
 * @package Solar_Access
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
     * Fetches the access list from the adapter into $this->list.
     * 
     * @param string $handle The username handle to fetch access
     * controls for.
     * 
     * @param array $roles The user roles to fetch access controls for.
     * 
     * @return void
     * 
     */
    public function load($handle, $roles)
    {
        $this->reset();
        // reverse so that last ones are checked first
        $this->list = array_reverse($this->fetch($handle, $roles));
    }
    
    /**
     * 
     * Tells whether or not to allow access to a class/action/submit combination.
     * 
     * @param string $class The class name of the control; use '*' for
     * all classes.
     * 
     * @param string $action The action within that class; use '*' for
     * all actions.
     * 
     * @param string $submit The submission value within the action; use
     * '*' for all submissions.
     * 
     * @return bool True if the current handle or role is allowed 
     * access, false if not.
     * 
     */
    public function isAllowed($class = '*', $action = '*', $submit = '*')
    {
        foreach ($this->list as $info) {
            $class_match  = ($info['class']  == $class  || $info['class'] == '*');
            $action_match = ($info['action'] == $action || $info['action'] == '*');
            $submit_match = ($info['submit'] == $submit || $info['submit'] == '*');
            if ($class_match && $action_match && $submit_match) {
                // all params match, return the flag (true or false)
                return (bool) $info['allow'];
            }
        }
        // no matching params, deny by default
        return false;
    }
    
    /**
     * 
     * Resets the current access controls to a blank array.
     * 
     * @return void
     * 
     */
    public function reset()
    {
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
}
