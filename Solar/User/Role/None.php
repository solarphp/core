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
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    public function fetch($handle)
    {
        return array();
    }
}
?>