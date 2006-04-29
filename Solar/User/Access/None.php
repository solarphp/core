<?php
/**
 * 
 * Class for denying all access to all users.
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
 * @version $Id: Role.php 655 2006-01-13 16:53:40Z pmjones $
 * 
 */

/**
 * 
 * Class for denying all access to all users.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User
 * 
 */
class Solar_User_Access_None extends Solar_Base {
    
    /**
     * 
     * Fetch access privileges for a user handle and roles.
     * 
     * @return array
     * 
     */
    public function fetch($handle, $roles)
    {
        return array(
            array(
                'allow'  => false,
                'class'  => '*',
                'action' => '*',
                'submit' => '*',
            ),
        );
    }
}
?>