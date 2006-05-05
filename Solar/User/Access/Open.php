<?php
/**
 * 
 * Class for allowing open access to all users.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User_Access
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
 * Class for allowing open access to all users.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User
 * 
 */
class Solar_User_Access_Open extends Solar_Base {
    
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
    public function fetch($handle, $roles)
    {
        return array(
            array(
                'allow'  => true,
                'class'  => '*',
                'action' => '*',
                'submit' => '*',
            ),
        );
    }
}
?>