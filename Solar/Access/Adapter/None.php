<?php
/**
 * 
 * Class for denying all access to all users.
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
 * Abstract access adapter class.
 */
Solar::loadClass('Solar_Access_Adapter');

/**
 * 
 * Class for denying all access to all users.
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 */
class Solar_Access_Adapter_None extends Solar_Access_Adapter {
    
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
                'allow'  => false,
                'class'  => '*',
                'action' => '*',
                'submit' => '*',
            ),
        );
    }
}
