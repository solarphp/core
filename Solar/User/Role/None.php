<?php
/**
 * 
 * No role source; always returns an empty array.
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
 * No role source; always returns an empty array.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User_Role_None extends Solar_Base {
    
    /**
     * 
     * Fetch the roles.
     *
     * @param string $user Username to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    public function fetch($user)
    {
        return array();
    }
}
?>