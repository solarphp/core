<?php
/**
 * 
 * Authenticate against nothing; defaults all authentication to "failed."
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Authentication adapter class.
 */
Solar::loadClass('Solar_Auth_Adapter');

/**
 * 
 * Authenticate against nothing; defaults all authentication to "failed."
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth_Adapter_None extends Solar_Auth_Adapter {
    
    /**
     * 
     * Validate a username and password.  Always fails.
     * 
     * @param string $handle Username to authenticate.
     * 
     * @param string $passwd The plain-text password to use.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function isValid($handle, $passwd)
    {
        return false;
    }
}
?>