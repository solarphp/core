<?php
/**
 * 
 * Abstract authentication adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Htpasswd.php 1223 2006-05-26 00:55:44Z pmjones $
 * 
 */

/**
 * 
 * Abstract authentication adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
abstract class Solar_Auth_Adapter extends Solar_Base {
    
    /**
     * 
     * Validates a user handle and password.
     * 
     * @param string $handle The user handle.
     * 
     * @param string $passwd The plain-text password.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    abstract public function isValid($handle, $passwd);
}
?>