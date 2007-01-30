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
 * @version $Id: Adapter.php 1379 2006-06-24 19:32:44Z pmjones $
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
     * Fetches the roles for a user handle.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    abstract public function fetch($handle);
}
?>