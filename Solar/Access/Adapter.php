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
?>